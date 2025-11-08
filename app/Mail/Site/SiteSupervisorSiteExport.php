<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteSupervisorSiteExport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $file_attachments;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file_attachments)
    {
        $this->file_attachments = $file_attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails/site/supervisor-site-export')->subject('SafeWorksite - Supervisor Site Export');

        // Attachments
        if ($this->file_attachments) {
            foreach ($this->file_attachments as $attachment) {
                if (file_exists($attachment))
                    $email->attach($attachment);
            }
        }

        return $email;
    }
}
