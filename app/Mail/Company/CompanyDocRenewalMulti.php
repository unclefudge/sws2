<?php

namespace App\Mail\Company;

use App\Models\Company\CompanyDoc;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyDocRenewalMulti extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $docs;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($docs)
    {
        $this->docs = $docs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/company/doc-renewal-multi')->subject('Standard Detail Documents due to be reviewed');
    }
}
