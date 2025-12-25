<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionPlumbing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class SiteInspectionPlumbingReportTrade extends Mailable implements ShouldQueue
{

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
        $pdf = PDF::loadView('pdf/site/inspection-plumbing', ['report' => $this->report])->setPaper('a4');

        return $this->markdown('emails/site/inspection-plumbing-report-trade')
            ->subject('SafeWorksite - Plumbing Inspection Report')
            ->attachData($pdf->output(), 'Plumbing Inspection Report.pdf', ['mime' => 'application/pdf']);
    }
}
