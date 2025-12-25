<?php

namespace App\Mail\Company;

use App\Models\Company\CompanyDoc;
use App\Services\FileBank;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyDocExpired extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $doc;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CompanyDoc $doc)
    {
        $this->doc = $doc;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $expired = ($this->doc->expiry->lt(Carbon::today())) ? "has Expired " . $this->doc->expiry->format('d/m/Y') : "due to expire " . $this->doc->expiry->format('d/m/Y');
        $email = $this->markdown('emails/company/doc-expired')->subject("SafeWorksite - Document $expired");

        if ($this->doc->attachment)
            FileBank::attachToEmail($email, "company/{$this->doc->company->id}/docs/{$this->doc->attachment}");

        return $email;
    }
}
