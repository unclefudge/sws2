<?php

namespace App\Mail\Site;

use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class SiteSupervisorSiteExport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $file_attachments;

    public function __construct(array $file_attachments)
    {
        $this->file_attachments = $file_attachments;
    }

    public function build()
    {
        $email = $this->markdown('emails.site.supervisor-site-export')->subject('SafeWorksite - Supervisor Site Export');

        foreach ($this->file_attachments as $path) {
            try {
                FileBank::attachToEmail($email, $path);
            } catch (\Throwable $e) {
                Log::warning('Supervisor export attachment failed', ['path' => $path, 'error' => $e->getMessage(),]);
            }
        }

        return $email;
    }
}
