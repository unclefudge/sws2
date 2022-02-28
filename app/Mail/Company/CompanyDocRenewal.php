<?php

namespace App\Mail\Company;

use App\Models\Company\CompanyDoc;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyDocRenewal extends Mailable implements ShouldQueue {

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
        $email = $this->markdown('emails/company/doc-renewal')->subject($this->doc->name.' is due to be reviewed');

        // Attachment
        $file_path = public_path($this->doc->attachment_url);
        if ($this->doc->attachment && file_exists($file_path))
            $email->attach($file_path);

        return $email;
    }
}
