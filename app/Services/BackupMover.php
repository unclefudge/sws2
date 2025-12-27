<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupMover
{


    public static function move(): int
    {
        $sourceDisk = 'local';
        $targetDisk = 'backup_spaces';
        $sourceDir = 'SafeWorksite';

        $files = Storage::disk($this->sourceDisk)->files($this->sourceDir);
        $moved = 0;

        foreach ($files as $file) {

            $filename = basename($file);

            // Optional safety: skip temp / partial files
            if (str_ends_with($filename, '.tmp')) {
                continue;
            }

            try {
                $stream = Storage::disk($sourceDisk)->readStream($file);

                if ($stream === false) {
                    throw new \RuntimeException('Failed to read file stream');
                }

                $uploaded = Storage::disk($targetDisk)->put(
                    $filename,
                    $stream
                );

                if ($uploaded) {
                    Storage::disk($sourceDisk)->delete($file);
                    $moved++;

                    Log::info('Backup moved to Spaces', [
                        'file' => $filename,
                    ]);
                }

            } catch (\Throwable $e) {
                Log::error('Backup upload failed', [
                    'file' => $filename,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $moved;
    }
}