<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Scheduled\ScheduledOperationDefinition;
use App\Models\Scheduled\ScheduledReportAttachment;
use App\Models\Scheduled\ScheduledReportMessage;
use App\Scheduled\ScheduledOperationRegistry;
use Illuminate\Support\Facades\Storage;

class ScheduledOperationsController extends Controller
{
    public function index()
    {
        $this->authoriseAdmin();

        return view('manage.scheduled-operations.index');
    }

    public function messagePreview(ScheduledReportMessage $message)
    {
        $this->authoriseAdmin();

        return $this->previewResponse($message);
    }

    public function clientMessagePreview(ScheduledReportMessage $message, ScheduledOperationRegistry $registry)
    {
        $this->authoriseClientMessage($message, $registry);

        return $this->previewResponse($message);
    }

    public function attachment(ScheduledReportAttachment $attachment, ScheduledOperationRegistry $registry)
    {
        $message = $attachment->message()->with('run')->firstOrFail();
        if (!$this->isAdmin()) {
            $this->authoriseClientMessage($message, $registry);
        }

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name,
            ['Content-Type' => $attachment->content_type ?: 'application/octet-stream']
        );
    }

    private function previewResponse(ScheduledReportMessage $message)
    {

        $body = $message->html_body ?: '<pre>' . e($message->text_body ?: 'No message body was captured.') . '</pre>';

        // The preview is isolated from SafeWorksite. Existing report styles and
        // remote images still render, but scripts cannot run in the admin session.
        return response($body)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Security-Policy', "default-src 'none'; img-src https: data: cid:; style-src 'unsafe-inline'; font-src https: data:; base-uri 'none'; form-action 'none'; frame-ancestors 'self'")
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function authoriseAdmin(): void
    {
        abort_unless($this->isAdmin(), 403);
    }

    private function authoriseClientMessage(ScheduledReportMessage $message, ScheduledOperationRegistry $registry): void
    {
        $this->authoriseClientReports();
        $taskKey = $message->run()->value('task_key');
        $clientKeys = collect($registry->clientReports())->pluck('key');
        abort_unless($clientKeys->contains($taskKey), 403);
        abort_unless(ScheduledOperationDefinition::query()->where('task_key', $taskKey)->whereNull('archived_at')->where('category', 'report')->where('client_configurable', true)->exists(), 403);
    }

    private function authoriseClientReports(): void
    {
        $user = auth()->user();
        $mode = config('scheduled_operations.report_settings_mode', 'legacy');
        $allowed = $user && $user->isCC() && match ($mode) {
            'preview' => $user->hasRole2('web-admin'),
            'live' => $user->hasRole2('web-admin') || $user->hasAnyPermissionType('settings'),
            default => false,
        };
        abort_unless($allowed, 403);
    }

    private function isAdmin(): bool
    {
        return auth()->check() && auth()->user()->hasRole2('web-admin');
    }
}
