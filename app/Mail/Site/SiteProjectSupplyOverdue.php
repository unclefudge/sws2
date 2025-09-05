<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteProjectSupplyOverdue extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $projsupply;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($projsupply)
    {
        $this->projsupply = $projsupply;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/project-supply-overdue')->subject("Project Supply Overdue");
    }
}
