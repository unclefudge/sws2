<?php

namespace App\Mail\Safety;

use App\Models\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SwmsOutofdate extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $company, $outofdate, $signature;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Company $company, $outofdate, $signature)
    {
        $this->company = $company;
        $this->outofdate = $outofdate;
        $this->signature = $signature;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/safety/swms-outofdate')->subject('SafeWorksite - Safe Work Method Statements');
    }
}
