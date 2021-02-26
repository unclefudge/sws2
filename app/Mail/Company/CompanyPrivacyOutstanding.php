<?php

namespace App\Mail\Company;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyPrivacyOutstanding extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $companies;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($companies)
    {
        $this->companies = $companies;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/company/privacy-outstanding')->subject('SafeWorksite - Outstanding Privacy Policies');
    }
}
