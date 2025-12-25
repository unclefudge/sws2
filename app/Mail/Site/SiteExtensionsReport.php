<?php

namespace App\Mail\Site;

use App\Models\Site\SiteExtension;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteExtensionsReport extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteExtension $report)
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
        $email = $this->markdown('emails/site/contract-extension-report')->subject('SafeWorksite - Process Contract Time Extensions Report');

        // Attach from Spaces
        FileBank::attachToEmail($email, "company/3/docs/contract-extension/{$this->report->attachment}");

        return $email;
    }
}
