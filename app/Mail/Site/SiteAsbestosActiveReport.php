<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteAsbestosActiveReport extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $abs;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($abs)
    {
        $this->abs = $abs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/asbestos-active-report')->subject('SafeWorksite - Asbestos Notifications Report');
    }
}
