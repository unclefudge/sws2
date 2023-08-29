<?php

namespace App\Mail\Company;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyMissingInfo extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $companies, $missing_info, $expired_docs1, $expired_docs2, $expired_docs3;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($companies, $missing_info, $expired_docs1, $expired_docs2, $expired_docs3)
    {
        $this->companies = $companies;
        $this->missing_info = $missing_info;
        $this->expired_docs1 = $expired_docs1;
        $this->expired_docs2 = $expired_docs2;
        $this->expired_docs3 = $expired_docs3;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/company/missing-info')->subject('SafeWorksite - Missing Company Info');
    }
}
