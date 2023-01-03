<?php

namespace App\Mail\Site;

use App\Models\Site\Incident\SiteIncident;
use App\Models\Misc\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteIncidentAction extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $incident;
    public $action;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteIncident $incident, Action $action)
    {
        $this->incident = $incident;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/incident-action')->subject('SafeWorksite - Incident Notification');
    }
}
