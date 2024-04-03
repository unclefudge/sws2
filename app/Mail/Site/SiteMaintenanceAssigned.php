<?php

namespace App\Mail\Site;

use App\Models\Site\SiteMaintenanceItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteMaintenanceAssigned extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $item;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteMaintenanceItem $item)
    {
        $this->item = $item;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/maintenance-assigned')->subject('SafeWorksite - Maintenance Request Notification');
    }
}
