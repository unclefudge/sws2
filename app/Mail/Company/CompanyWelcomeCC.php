<?php

namespace App\Mail\Company;

use App\Models\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyWelcomeCC extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $company, $parent_company, $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Company $company, Company $parent_company, $name)
    {
        $this->company = $company;
        $this->parent_company = $parent_company;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/company/welcome-cc')->subject('Welcome to SafeWorksite');
    }
}
