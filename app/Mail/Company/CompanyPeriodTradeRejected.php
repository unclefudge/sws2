<?php

namespace App\Mail\Company;

use App\Models\Company\CompanyDocPeriodTrade;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyPeriodTradeRejected extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $ptc;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CompanyDocPeriodTrade $ptc)
    {
        $this->ptc = $ptc;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails/company/ptc-rejected')->subject('SafeWorksite - Contract Not Approved');

        // Attachment
        if ($this->ptc->attachment)
            FileBank::attachToEmail($email, "company/{$this->ptc->company->id}/docs/{$this->ptc->attachment}");

        return $email;
    }
}
