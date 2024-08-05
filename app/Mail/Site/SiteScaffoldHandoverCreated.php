<?php

namespace App\Mail\Site;

use App\Models\Site\SiteScaffoldHandover;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteScaffoldHandoverCreated extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteScaffoldHandover $report)
    {
        $this->report = $report;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->report->site->name . "- Scaffold Handover Certificate";
        return $this->markdown('emails/site/scaffold-handover-created')->subject($subject);
    }
}
