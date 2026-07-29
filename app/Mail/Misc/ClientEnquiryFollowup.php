<?php

namespace App\Mail\Misc;

use App\Models\Misc\WebsiteFormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientEnquiryFollowup extends Mailable
{
    use Queueable, SerializesModels;

    public WebsiteFormSubmission $submission;

    public function __construct(WebsiteFormSubmission $submission)
    {
        $this->submission = $submission;
    }

    public function build()
    {
        return $this->subject('**Part 1 only** Your Home Addition Enquiry')
            ->from('inform@capecod.com.au', 'Cape Cod Australia')
            //->replyTo('inform@capecod.com.au', 'Cape Cod Australia')
            ->view('emails/misc/client-enquiry-followup');
    }
}