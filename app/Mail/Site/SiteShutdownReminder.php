<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteShutdownReminder extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $site_list;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($site_list)
    {
        $this->site_list = $site_list;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/shutdown-reminder')->subject('SafeWorksite - URGENT - Complete Site Shutdown');
    }
}
