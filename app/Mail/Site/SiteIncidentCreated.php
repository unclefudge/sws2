<?php

namespace App\Mail\Site;

use App\Models\Site\Incident\SiteIncident;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteIncidentCreated extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $incident;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteIncident $incident)
    {
        $this->incident = $incident;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/incident-created')->subject('SafeWorksite - Incident Notification');
    }
}
