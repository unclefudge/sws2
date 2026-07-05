<?php

namespace App\Mail\Misc;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestDesignerSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public array $summary;

    public function __construct(array $summary)
    {
        $this->summary = $summary;
    }

    public function build()
    {
        return $this->subject('Request a Designer Visit - Cape Cod')
            ->from('noreply@safeworksite.com.au', 'Cape Cod Australia')
            ->replyTo('clientservices@capecod.com.au', 'Cape Cod Australia')
            //->bcc('inform@capecod.com.au')
            ->view('emails/misc/request-designer-submitted');
    }
}