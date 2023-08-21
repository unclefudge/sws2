<?php

namespace App\Mail\Site;

use App\Models\Site\SiteExtension;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteExtensionsFinalReminder extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $report, $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteExtension $report, $message)
    {
        $this->report = $report;
        $this->message = $message;
        //dd($message);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
            return $this->markdown('emails/site/contract-extension-final-reminder')->subject('SafeWorksite - URGENT - Contract Time Extensions Sign Off');
    }
}
