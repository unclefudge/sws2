<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionElectrical;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteInspectionElectricalAssignedTrade extends Mailable implements ShouldQueue
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
        $email = $this->markdown('emails/site/inspection-electrical-assigned')->subject('SafeWorksite - Electrical Inspection Report Assigned');

        // Add Attachments
        foreach ($this->report->attachments as $file) {
            if ($file->directory && $file->attachment) {
                FileBank::attachToEmail($email, "$file->directory/$file->attachment");
            }
        }

        return $email;
    }
}
