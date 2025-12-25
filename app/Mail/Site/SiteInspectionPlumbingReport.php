<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionPlumbing;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class SiteInspectionPlumbingReport extends Mailable implements ShouldQueue
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
        $email = $this->markdown('emails/site/inspection-plumbing-report')->subject('SafeWorksite - Plumbing Inspection Report');

        // Attachments - Uploaded by Plumber
        $inspected_by = $this->report->inspected_by; // Plumbers user_id
        foreach ($this->report->attachments as $doc) {
            if ($doc->created_by !== $inspected_by || !$doc->attachment)
                continue;

            // Build FileBank path
            $path = trim($doc->directory, '/') . '/' . $doc->attachment;

            FileBank::attachToEmail($email, $path);
        }

        // Plumbing report
        $pdf = PDF::loadView('pdf/site/inspection-plumbing', ['report' => $this->report])->setPaper('a4');

        return $email->attachData($pdf->output(), 'Plumbing Inspection Report.pdf', ['mime' => 'application/pdf']);
    }
}
