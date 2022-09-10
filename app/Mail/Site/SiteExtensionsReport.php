<?php

namespace App\Mail\Site;

use App\Models\Site\SiteExtension;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteExtensionsReport extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $report, $file_attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteExtension $report, $file_attachment)
    {
        $this->report = $report;
        $this->file_attachment = $file_attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->file_attachment && file_exists($this->file_attachment))
            return $this->markdown('emails/site/extensions-report')->subject('SafeWorksite - Contract Time Extensions Report')->attach($this->file_attachment);
    }
}
