<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteScaffoldHandoverOutstanding extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $outstanding;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($outstanding)
    {
        $this->outstanding = $outstanding;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/scaffold-handover-outstanding')->subject('SafeWorksite - Scaffold Handover Certificate Outstanding');
    }
}
