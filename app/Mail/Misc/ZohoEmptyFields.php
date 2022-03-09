<?php

namespace App\Mail\Misc;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ZohoEmptyFields extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $mesg;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mesg)
    {
        $this->mesg = $mesg;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/misc/zoho-empty-fields')->subject('SafeWorksite - Zoho Missing Data');
    }
}
