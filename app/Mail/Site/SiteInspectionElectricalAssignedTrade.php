<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionElectrical;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteInspectionElectricalAssignedTrade extends Mailable implements ShouldQueue {

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
        $email = $this->markdown('emails/site/inspection-electrical-assigned')->subject('SafeWorksite - Electrical Inspection Report Assigned');

        // Attachments - Report
        if ($this->report->docs()->count()) {
            foreach ($this->report->docs() as $doc) {
                if ($doc->type == 'doc' && file_exists(public_path($doc->AttachmentUrl)))
                    $email->attach(public_path($doc->AttachmentUrl));
            }
        }

        return $email;
    }
}
