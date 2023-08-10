<?php

namespace App\Mail\Site;

use App\Models\Site\SiteInspectionElectrical;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteInspectionActive extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $electrical, $plumbing, $type, $overdue_date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($electrical, $plumbing, $type, $overdue_date = null)
    {
        $this->electrical = $electrical;
        $this->plumbing = $plumbing;
        $this->type = $type;
        $this->overdue_date = $overdue_date;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $type = $this->type;
        return $this->markdown('emails/site/inspection-active')->subject("SafeWorksite - Open $type Inspection Reports");
    }
}
