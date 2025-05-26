<?php

namespace App\Mail\Company;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyDocsPending extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $pending_info;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pending_info)
    {
        $this->pending_info = $pending_info;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/company/docs-pending')->subject('SafeWorksite - Company Docs Pending');
    }
}
