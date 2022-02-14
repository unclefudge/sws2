<?php

namespace App\Mail\Site;

use App\Models\Site\SiteScaffoldHandover;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteScaffoldHandoverEmail extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $report, $file_attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteScaffoldHandover $report, $file_attachment)
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
            return $this->markdown('emails/site/scaffold-handover')->subject('SafeWorksite - Scaffold Handover Certificate')->attach($this->file_attachment);

        return $this->markdown('emails/site/scaffold-handover')->subject('SafeWorksite - Scaffold Handover Certificate');
    }
}
