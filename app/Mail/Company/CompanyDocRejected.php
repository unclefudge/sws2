<?php

namespace App\Mail\Company;

use App\Models\Company\CompanyDoc;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyDocRejected extends Mailable implements ShouldQueue
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
        $email = $this->markdown('emails/company/doc-rejected')->subject('SafeWorksite - Document Not Approved');

        if ($this->doc->attachment)
            FileBank::attachToEmail($email, "company/{$this->doc->company->id}/docs/{$this->doc->attachment}");

        return $email;
    }
}
