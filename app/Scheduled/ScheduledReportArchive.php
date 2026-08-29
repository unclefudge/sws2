<?php

namespace App\Scheduled;

use App\Models\Scheduled\ScheduledReportAttachment;
use App\Models\Scheduled\ScheduledReportMessage;
use App\Models\Scheduled\ScheduledRun;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stringable;
use Symfony\Component\Mime\Part\File;
use Throwable;

class ScheduledReportArchive
{
    public function capture(ScheduledReportMessage $message, iterable $parts): array
    {
        if (!Schema::hasTable('scheduled_report_attachments')) {
            return collect($parts)->filter(fn($part) => $this->isArchivablePart($part))->map(fn($part) => [
                'name' => $this->safeFilename(method_exists($part, 'getFilename') ? $part->getFilename() : null),
                'content_type' => method_exists($part, 'getMediaType') ? $part->getMediaType().'/'.$part->getMediaSubtype() : null,
                'archived' => false,
            ])->values()->all();
        }

        $metadata = [];
        $errors = [];

        foreach ($parts as $part) {
            // Embedded logos/signatures are part of the email body, not files a
            // user would expect to find in the report attachment history.
            if (!$this->isArchivablePart($part)) continue;

            $name = $this->safeFilename(method_exists($part, 'getFilename') ? $part->getFilename() : null);
            $contentType = method_exists($part, 'getMediaType')
                ? $part->getMediaType().'/'.$part->getMediaSubtype()
                : 'application/octet-stream';
            $disk = (string) config('scheduled_operations.attachment_disk', 'scheduled_reports');
            $path = null;

            try {
                $contents = $this->contents($part);
                $size = strlen($contents);
                $maximum = max(1, (int) config('scheduled_operations.attachment_max_bytes', 31457280));
                if ($size > $maximum) {
                    throw new \RuntimeException('Attachment exceeds the configured archive size limit.');
                }

                $run = $message->run()->firstOrFail();
                $path = implode('/', [
                    'scheduled-reports',
                    Str::slug($run->task_key) ?: 'operation',
                    $run->id,
                    $message->uuid,
                    Str::uuid().'-'.$name,
                ]);

                if (!Storage::disk($disk)->put($path, $contents)) {
                    throw new \RuntimeException("The attachment could not be written to disk [$disk].");
                }

                $attachment = $message->archivedAttachments()->create([
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $name,
                    'content_type' => $contentType,
                    'size_bytes' => $size,
                    'sha256' => hash('sha256', $contents),
                ]);

                $metadata[] = ['id' => $attachment->id, 'name' => $name, 'content_type' => $contentType, 'size_bytes' => $size, 'archived' => true];
            } catch (Throwable $exception) {
                if ($path) {
                    try {
                        Storage::disk($disk)->delete($path);
                    } catch (Throwable) {
                    }
                }

                $errors[] = "$name: {$exception->getMessage()}";
                $metadata[] = ['name' => $name, 'content_type' => $contentType, 'archived' => false, 'error' => $exception->getMessage()];
                Log::warning('Scheduled report attachment could not be archived', [
                    'scheduled_report_message_id' => $message->id,
                    'filename' => $name,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($errors && Schema::hasColumn('scheduled_report_messages', 'attachment_capture_error')) {
            try {
                $message->update(['attachment_capture_error' => implode("\n", $errors)]);
            } catch (Throwable $exception) {
                Log::warning('Scheduled attachment archive error could not be recorded', ['error' => $exception->getMessage()]);
            }
        }

        return $metadata;
    }

    public function pruneOnceDaily(string $taskKey): void
    {
        $seconds = max(300, (int) config('scheduled_operations.attachment_cleanup_interval', 86400));
        if (!Cache::add('scheduled-report-prune:all', true, $seconds)) {
            return;
        }

        try {
            ScheduledRun::query()->whereHas('messages')->distinct()->pluck('task_key')->each(fn(string $key) => $this->prune($key));
        } catch (Throwable $exception) {
            Log::warning('Scheduled report archive cleanup failed', ['task_key' => $taskKey, 'error' => $exception->getMessage()]);
        }
    }

    public function prune(string $taskKey): void
    {
        $protectedRunIds = ScheduledRun::query()
            ->where('task_key', $taskKey)
            ->whereHas('messages')
            ->latest('scheduled_for')->latest('id')
            ->limit(max(1, (int) config('scheduled_operations.attachment_min_runs', 5)))
            ->pluck('id');

        ScheduledReportAttachment::query()
            ->where('created_at', '<', now()->subDays(max(1, (int) config('scheduled_operations.attachment_retention_days', 30))))
            ->whereHas('message.run', fn($query) => $query->where('task_key', $taskKey))
            ->whereHas('message', fn($query) => $query->whereNotIn('scheduled_run_id', $protectedRunIds))
            ->chunkById(100, function ($attachments) {
                foreach ($attachments as $attachment) {
                    Storage::disk($attachment->disk)->delete($attachment->path);
                    $attachment->delete();
                }
            });

        ScheduledReportMessage::query()
            ->where('created_at', '<', now()->subDays(max(1, (int) config('scheduled_operations.history_days', 90))))
            ->whereHas('run', fn($query) => $query->where('task_key', $taskKey))
            ->whereNotIn('scheduled_run_id', $protectedRunIds)
            ->chunkById(100, function ($messages) {
                foreach ($messages as $message) {
                    foreach ($message->archivedAttachments as $attachment) {
                        Storage::disk($attachment->disk)->delete($attachment->path);
                    }
                    $message->archivedAttachments()->delete();
                    $message->recipients()->delete();
                    $message->delete();
                }
            });
    }

    private function contents($part): string
    {
        if (!method_exists($part, 'getBody')) {
            throw new \RuntimeException('Unsupported attachment body.');
        }

        $body = $part->getBody();
        if ($body instanceof File) {
            $contents = file_get_contents($body->getPath());
            if ($contents === false) throw new \RuntimeException('Attachment file could not be read.');
            return $contents;
        }
        if (is_resource($body)) {
            $position = ftell($body);
            rewind($body);
            $contents = stream_get_contents($body);
            if ($position !== false) fseek($body, $position);
            if ($contents === false) throw new \RuntimeException('Attachment stream could not be read.');
            return $contents;
        }
        if (is_string($body) || $body instanceof Stringable) return (string) $body;

        throw new \RuntimeException('Unsupported attachment body type.');
    }

    private function safeFilename(?string $filename): string
    {
        $filename = basename(trim((string) $filename)) ?: 'attachment';
        $filename = preg_replace('/[^A-Za-z0-9._ -]+/', '-', Str::ascii($filename));
        $filename = trim((string) $filename, '. -');

        return mb_substr($filename ?: 'attachment', 0, 180);
    }

    private function isArchivablePart($part): bool
    {
        return !method_exists($part, 'getDisposition') || $part->getDisposition() !== 'inline';
    }
}
