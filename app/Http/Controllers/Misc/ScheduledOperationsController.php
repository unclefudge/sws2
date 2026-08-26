<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Scheduled\ScheduledReportMessage;

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

        $body = $message->html_body ?: '<pre>' . e($message->text_body ?: 'No message body was captured.') . '</pre>';

        // The preview is isolated from SafeWorksite. Existing report styles and
        // remote images still render, but scripts cannot run in the admin session.
        return response($body)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Security-Policy', "default-src 'none'; img-src https: data: cid:; style-src 'unsafe-inline'; font-src https: data:; base-uri 'none'; form-action 'none'");
    }

    private function authoriseAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasRole2('web-admin'), 403);
    }
}
