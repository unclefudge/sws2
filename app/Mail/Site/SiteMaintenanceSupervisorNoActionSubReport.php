<?php

namespace App\Mail\Site;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteMaintenanceSupervisorNoActionSubReport extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($body)
    {
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //if ($this->file_attachment && file_exists($this->file_attachment))
        //    return $this->markdown('emails/site/maintenance-supervisor-noaction-sub')->subject('SafeWorksite - Maintenance Supervisor No Actions')->attach($this->file_attachment);

        return $this->markdown('emails/site/maintenance-supervisor-noaction-sub')->subject('SafeWorksite - Maintenance Supervisor No Actions');
    }
}
