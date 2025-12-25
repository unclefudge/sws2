<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionElectrical;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class SiteInspectionElectricalReportTrade extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteInspectionElectrical $report)
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
        $pdf = PDF::loadView('pdf/site/inspection-electrical', ['report' => $this->report])->setPaper('a4');

        return $this->markdown('emails/site/inspection-electrical-report-trade')
            ->subject('SafeWorksite - Electrical Inspection Report')
            ->attachData($pdf->output(), 'Electrical Inspection Report.pdf', ['mime' => 'application/pdf']);
    }
}
