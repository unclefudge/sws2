<?php

namespace App\Mail\Site;

use App\Models\Site\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteInspectionCancelled extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $site, $cancelled;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Site $site, $cancelled)
    {
        $this->site = $site;
        $this->cancelled = $cancelled;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/inspection-cancelled')->subject('SafeWorksite - Cancelled Inspection Reports');
    }
}
