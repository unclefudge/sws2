<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionPlumbing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteInspectionPlumbingReopened extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteInspectionPlumbing $report)
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
        return $this->markdown('emails/site/inspection-plumbing-reopened')->subject('SafeWorksite - Plumbing Inspection Report Re-opened');
    }
}
