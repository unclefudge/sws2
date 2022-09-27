<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionPlumbing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteInspectionPlumbingReport extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $report, $file_attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteInspectionPlumbing $report, $file_attachment)
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
        $email = $this->markdown('emails/site/inspection-plumbing-report')->subject('SafeWorksite - Plumbing Inspection Report');

        // Attachments - Report
        if ($this->file_attachment && file_exists($this->file_attachment))
            $email->attach($this->file_attachment);

        // Attachments - Uploaded by Plumber
        $inspected_by = $this->report->inspected_by; // Plumbers user_id
        if ($this->report->docs()->count()) {
            foreach ($this->report->docs() as $doc) {
                if ($doc->created_by == $inspected_by && file_exists(public_path($doc->attachment_url)))
                    $email->attach(public_path($doc->attachment_url));
            }
        }
        return $email;
    }
}
