<?php

namespace App\Mail\Site;

use App\Models\Site\SiteExtension;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteExtensionsReminder extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $report, $site_list;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteExtension $report, $site_list)
    {
        $this->report = $report;
        $this->site_list = $site_list;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
            return $this->markdown('emails/site/contract-extension-reminder')->subject('SafeWorksite - URGENT - Complete Contract Time Extensions');
    }
}
