<?php

namespace App\Mail\Site;

use App\Models\Site\SiteAccident;
use App\Models\Misc\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteAccidentAction extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $accident;
    public $action;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteAccident $accident, Action $action)
    {
        $this->accident = $accident;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/accident-action')->subject('SafeWorksite - Accident Notification');
    }
}