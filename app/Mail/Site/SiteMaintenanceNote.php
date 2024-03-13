<?php

namespace App\Mail\Site;

use App\Models\Misc\Action;
use App\Models\Site\SiteMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteMaintenanceNote extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $main, $action;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteMaintenance $main, Action $action)
    {
        $this->main = $main;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/maintenance-note')->subject('SafeWorksite - Maintenance Request Note');
    }
}
