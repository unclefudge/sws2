<?php

namespace App\Mail\Company;

use App\Models\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyLeave extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $company, $action;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Company $company, $action)
    {
        $this->company = $company;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/company/company-leave')->subject('SafeWorksite - Company Leave');
    }
}
