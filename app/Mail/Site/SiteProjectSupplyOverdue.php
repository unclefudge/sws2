<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteProjectSupplyOverdue extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $lock, $prac;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($lock, $prac)
    {
        $this->lock = $lock;
        $this->prac = $prac;
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
