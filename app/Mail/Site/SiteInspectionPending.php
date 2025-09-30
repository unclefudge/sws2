<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteInspectionPending extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $elPendingAdmin, $elPendingTech, $elClientNot, $plPendingAdmin, $plPendingTech, $plClientNot;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($elPendingAdmin, $elPendingTech, $elClientNot, $plPendingAdmin, $plPendingTech, $plClientNot)
    {
        $this->elPendingAdmin = $elPendingAdmin;
        $this->elPendingTech = $elPendingTech;
        $this->elClientNot = $elClientNot;
        $this->plPendingAdmin = $plPendingAdmin;
        $this->plPendingTech = $plPendingTech;
        $this->plClientNot = $plClientNot;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/inspection-pending')->subject("SafeWorksite - Pending Inspection Reports");
    }
}
