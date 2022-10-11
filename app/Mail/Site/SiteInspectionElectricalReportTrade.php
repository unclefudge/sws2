<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionElectrical;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteInspectionElectricalReportTrade extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $report, $file_attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteInspectionElectrical $report, $file_attachment)
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
        $email = $this->markdown('emails/site/inspection-electrical-report-trade')->subject('SafeWorksite - Electrical Inspection Report');

        // Attachments - Report
        if ($this->file_attachment && file_exists($this->file_attachment))
            $email->attach($this->file_attachment);

        return $email;
    }
}
