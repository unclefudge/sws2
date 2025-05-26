<?php

namespace App\Mail\Company;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyMissingInfoPlanner extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $missing_info, $pending_info;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($missing_info, $pending_info)
    {
        $this->missing_info = $missing_info;
        $this->pending_info = $pending_info;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/company/missing-info-planner')->subject('SafeWorksite - Missing and Pending Company Info');
    }
}
