<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileBank
{
    /* ============================================================
     |  Path helpers
     |============================================================ */

    /**
     * Normalize a file path:
     * - no leading slash
     * - forward slashes only
     * - no directory traversal
     */
    public static function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        if (str_contains($path, '..')) {
            throw new \InvalidArgumentException('Invalid file path.');
        }

        return preg_replace('#/+#', '/', $path);
    }

    /**
     * Paths that must always stay local (tmp/, log/, etc).
     */
    public static function isTempPath(string $path): bool
    {
        $path = self::normalizePath($path);

        foreach (config('filesystems.filebank_temp_prefixes', []) as $prefix) {
            $prefix = rtrim(trim($prefix), '/') . '/';

            if ($prefix !== '/' && Str::startsWith($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /* ============================================================
     |  Disk selection
     |============================================================ */

    /**
     * Disk for new uploads (unless temp path).
     */
    public static function defaultDisk(): string
    {
        return config('filesystems.filebank_default_disk', 'filebank_local');
    }

    /**
     * Disk for legacy / fallback reads.
     */
    public static function fallbackDisk(): string
    {
        return config('filesystems.filebank_fallback_disk', 'filebank_local');
    }

    /**
     * Disk to write to for a given path.
     */
    protected static function writeDiskFor(string $path): string
    {
        return self::isTempPath($path) ? 'filebank_local' : self::defaultDisk();
    }

    /**
     * Ordered list of disks to try when reading.
     */
    public static function readDisks(): array
    {
        $default = self::defaultDisk();
        $fallback = self::fallbackDisk();

        return array_values(array_unique([$default, $fallback]));
    }

    protected static function disk(string $disk): Filesystem
    {
        return Storage::disk($disk);
    }

    /* ============================================================
     |  Write operations
     |  - put calls putStream but the difference bewteen using put vs putSteam is:
     *  - put closes the stream afterwards but if you call putSteam directly it doesn't
     |============================================================ */

    /**
     * Store an UploadedFile or File at a given path.
     */
    public static function put(string $path, UploadedFile|File $file, array $options = []): string
    {
        $path = self::normalizePath($path);

        $stream = fopen($file->getRealPath(), 'rb');

        if (!$stream) {
            throw new \RuntimeException('Unable to open file stream.');
        }

        try {
            return self::putStream($path, $stream, $options);
        } finally {
            fclose($stream);
        }
    }

    /**
     * Store a file using a stream (REQUIRED for Spaces correctness).
     */
    public static function putStream(string $path, $stream, array $options = []): string
    {
        $path = self::normalizePath($path);

        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream resource required.');
        }

        if (str_ends_with($path, '/')) {
            throw new \InvalidArgumentException('Path must include a filename.');
        }

        $disk = self::writeDiskFor($path);
        $ok = self::disk($disk)->put($path, $stream, $options);

        if (!$ok) {
            throw new \RuntimeException("Failed to write file stream: {$path}");
        }

        return $path;
    }

    /**
     * Write raw contents.
     */
    public static function putContents(string $path, string $contents, array $options = []): string
    {
        $path = self::normalizePath($path);
        $disk = self::writeDiskFor($path);

        if (!self::disk($disk)->put($path, $contents, $options))
            throw new \RuntimeException("Failed to write file: {$path}");

        return $path;
    }

    /* ============================================================
     | Store new uploaded file
     |============================================================ */
    public static function storeUploadedFile(UploadedFile|File $file, string $basePath, ?string $forcedFilename = null, bool $resizeImage = false, int $maxWidth = 1024): string
    {
        $basePath = self::normalizePath($basePath);

        // Figure out a “source name” depending on the file type
        $sourceName = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename();

        // If forcedFilename provided, use that as the base name (and extension if present)
        if ($forcedFilename) {
            $originalName = sanitizeFilename(pathinfo($forcedFilename, PATHINFO_FILENAME));
            $extension = strtolower(pathinfo($forcedFilename, PATHINFO_EXTENSION)) ?: strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));
        } else {
            $originalName = sanitizeFilename(pathinfo($sourceName, PATHINFO_FILENAME));
            $extension = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));
        }

        // Fallback: if extension still missing, try UploadedFile’s extension
        if (!$extension && $file instanceof UploadedFile)
            $extension = strtolower($file->getClientOriginalExtension());

        $filename = $extension ? "{$originalName}.{$extension}" : $originalName;
        $path = "{$basePath}/{$filename}";
        $counter = 1;

        // Ensure uniqueness across disks
        while (self::exists($path)) {
            $filename = $extension ? "{$originalName}-{$counter}.{$extension}" : "{$originalName}-{$counter}";
            $path = "{$basePath}/{$filename}";
            $counter++;
        }

        // Prepare/resize on local temp path (works for both UploadedFile and File)
        self::prepareFilePath($file->getRealPath(), $resizeImage, $maxWidth);

        // Stream-safe write to Spaces (via put -> putStream)
        self::put($path, new File($file->getRealPath()));

        return $filename;
    }

    /* ============================================================
     | Replace existing uploaded file
     |============================================================ */
    public static function replaceUploadedFile(UploadedFile $file, string $basePath, ?string $existingFilename = null, ?string $forcedFilename = null, bool $resizeImage = false, int $maxWidth = 1024): string
    {
        $basePath = self::normalizePath($basePath);

        // Delete old file (Spaces-first, safe)
        if ($existingFilename)
            self::delete("{$basePath}/{$existingFilename}");

        return self::storeUploadedFile($file, $basePath, $forcedFilename, $resizeImage, $maxWidth);
    }

    /* ============================================================
     | Local image preparation (Intervention)
     |============================================================ */
    protected static function prepareFilePath(?string $path, bool $resizeImage, int $maxWidth): void
    {
        if (!$resizeImage || !$path)
            return;

        // Only attempt resize if it’s actually an image
        if (!@exif_imagetype($path))
            return;

        Image::make($path)
            ->resize($maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save($path);
    }

    /* ============================================================
     |  Read operations
     |============================================================ */

    public static function exists(string $path): bool
    {
        $path = self::normalizePath($path);

        foreach (self::readDisks() as $disk) {
            if (self::disk($disk)->exists($path))
                return true;
        }

        return false;
    }

    public static function get(string $path): string
    {
        $path = self::normalizePath($path);

        foreach (self::readDisks() as $disk) {
            if (self::disk($disk)->exists($path))
                return self::disk($disk)->get($path);
        }

        throw new \RuntimeException("File not found: {$path}");
    }

    // Calculate total size (bytes) of a folder/prefix.
    public static function folderSize(string $prefix): int
    {
        $prefix = self::normalizePath($prefix);
        $size = 0;
        $disks = self::readDisks();

        foreach ($disks as $disk) {
            // If folder doesn't exist on this disk, skip
            if (!Storage::disk($disk)->exists($prefix))
                continue;

            foreach (Storage::disk($disk)->allFiles($prefix) as $file) {
                try {
                    $size += Storage::disk($disk)->size($file);
                } catch (\Throwable $e) {
                    // ignore unreadable files
                }
            }

            // IMPORTANT: Stop after first disk found to avoid double-counting
            break;
        }

        return $size;
    }

    public static function fileSize(string $path): ?int
    {
        $path = self::normalizePath($path);

        foreach (self::readDisks() as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                try {
                    return Storage::disk($disk)->size($path);
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        return null;
    }

    /* ============================================================
    | Move / rename file (Spaces-safe)
    |============================================================ */
    public static function move(string $from, string $to): bool
    {
        $from = self::normalizePath($from);
        $to = self::normalizePath($to);

        if ($from === $to)
            return true;

        // Ensure source exists
        if (!self::exists($from))
            return false;

        // Read stream from source (default or fallback)
        foreach (self::readDisks() as $disk) {
            if (!self::disk($disk)->exists($from))
                continue;

            $stream = self::disk($disk)->readStream($from);

            if (!is_resource($stream))
                throw new \RuntimeException("Unable to read stream: {$from}");

            try {
                // Write to destination (default disk)
                $writeDisk = self::writeDiskFor($to);

                if (!self::disk($writeDisk)->put($to, $stream))
                    throw new \RuntimeException("Failed to write moved file: {$to}");

            } finally {
                fclose($stream);
            }

            // Delete original (all disks)
            self::delete($from);

            return true;
        }

        return false;
    }


    /* ============================================================
     |  Delete operations
     |============================================================ */

    public static function delete(string $path): bool
    {
        $path = self::normalizePath($path);

        $deleted = false;

        foreach (self::readDisks() as $disk) {
            if (self::disk($disk)->exists($path)) {
                self::disk($disk)->delete($path);
                $deleted = true;
            }
        }

        return $deleted;
    }

    public static function deleteDirectory(string $path): bool
    {
        $path = self::normalizePath($path);

        if (self::isTempPath($path))
            return false;

        $deleted = false;

        foreach (self::readDisks() as $disk) {
            if (self::disk($disk)->exists($path)) {
                self::disk($disk)->deleteDirectory($path);
                $deleted = true;
            }
        }

        return $deleted;
    }


    /* ============================================================
     |  URLs & downloads
     |============================================================ */

    /**
     * URL for linking to a file.
     *
     * - Spaces → signed URL
     * - Local → /filebank proxy route
     */
    public static function url(string $path, int $minutes = 10): string
    {
        $path = self::normalizePath($path);

        foreach (self::readDisks() as $disk) {
            if (!self::disk($disk)->exists($path))
                continue;

            if ($disk === 'filebank_spaces')
                return self::temporaryUrl($path, $minutes);

            return '/filebank/' . $path;
        }

        return '/filebank/' . $path;
    }

    /**
     * Generate signed Spaces URL.
     */
    public static function temporaryUrl(string $path, int $minutes = 10): string
    {
        $path = self::normalizePath($path);

        if (!self::disk('filebank_spaces')->exists($path))
            throw new \RuntimeException("File not found in Spaces: {$path}");

        return self::disk('filebank_spaces')->temporaryUrl(
            $path,
            now()->addMinutes($minutes)
        );
    }


    public static function streamInline(string $path): StreamedResponse
    {
        $path = self::normalizePath($path);

        foreach (self::readDisks() as $disk) {
            if (!self::disk($disk)->exists($path))
                continue;

            $stream = self::disk($disk)->readStream($path);

            if (!is_resource($stream))
                throw new \RuntimeException("Unable to open stream: {$path}");

            $mime = self::disk($disk)->mimeType($path) ?? 'application/octet-stream';

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, max-age=3600',
            ]);
        }

        abort(404);
    }

    public static function attachToEmail($mail, string $filePath): void
    {
        $filePath = self::normalizePath($filePath);

        if (!self::exists($filePath))
            return;

        $filename = basename($filePath);

        // Create temp file
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir))
            mkdir($tmpDir, 0755, true);

        $tmpPath = $tmpDir . '/' . uniqid('fb_') . '-' . $filename;
        file_put_contents($tmpPath, self::get($filePath));

        $mail->attach($tmpPath, ['as' => $filename,]);

        register_shutdown_function(fn() => @unlink($tmpPath));
    }

    /* ============================================================
     |  Migration helpers
     |============================================================ */

    /**
     * Migrate a single file from fallback → default disk.
     */
    public static function migrateOne(string $path): bool
    {
        $path = self::normalizePath($path);

        if (self::isTempPath($path))
            return false;

        $default = self::defaultDisk();
        $fallback = self::fallbackDisk();

        if ($default === $fallback)
            return false;

        if (self::disk($default)->exists($path))
            return false;

        if (!self::disk($fallback)->exists($path))
            return false;

        $stream = self::disk($fallback)->readStream($path);

        if (!is_resource($stream))
            throw new \RuntimeException("Unable to read source stream: {$path}");

        $ok = self::disk($default)->put($path, $stream);
        fclose($stream);

        if (!$ok)
            throw new \RuntimeException("Failed to migrate file: {$path}");

        return true;
    }

    // Migrate everything
    // - $files = Storage::disk('filebank_local')->allFiles('');
    // - $result = FileBank::migrateMany($files);
    //
    // Migrate by top-level folder
    // - $files = Storage::disk('filebank_local')->allFiles('site');
    // - $result = FileBank::migrateMany($files);
    // - eg allFiles('site') or allFiles('company')
    //
    // Migrate by string pattern
    // - $files = collect(Storage::disk('filebank_local')->allFiles('site'))->filter(fn ($path) => str_contains($path, '/hazard/'));
    // - $result = FileBank::migrateMany($files);
    //
    // Dry Run
    // - $result = FileBank::migrateMany($files, dryRun: true);
    // - dd(['count' => count($files),'example' => array_slice($files->toArray(), 0, 10)]);
    public static function migrateMany(iterable $paths, bool $dryRun = false): array
    {
        $results = ['migrated' => [], 'skipped' => [], 'failed' => [],];

        foreach ($paths as $path) {
            try {
                if ($dryRun) {
                    $results['skipped'][] = $path;
                    continue;
                }

                if (self::migrateOne($path))
                    $results['migrated'][] = $path;
                else
                    $results['skipped'][] = $path;

            } catch (\Throwable $e) {
                $results['failed'][$path] = $e->getMessage();
            }
        }

        return $results;
    }
}
