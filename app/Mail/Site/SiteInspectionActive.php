<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionElectrical;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteInspectionActive extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $electrical, $plumbing;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($electrical, $plumbing)
    {
        $this->electrical = $electrical;
        $this->plumbing = $plumbing;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/inspection-active')->subject('SafeWorksite - Open Inspection Reports');
    }
}
