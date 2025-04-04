<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteQaOutstanding extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $file_attachments, $outQas, $outSupers, $holdQas, $holdSupers;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file_attachments, $outQas, $outSupers, $holdQas, $holdSupers)
    {
        $this->file_attachments = $file_attachments;
        $this->outQas = $outQas;
        $this->outSupers = $outSupers;
        $this->holdQas = $holdQas;
        $this->holdSupers = $holdSupers;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails/site/qa-outstanding')->subject('SafeWorksite - Outstanding & On Hold QA');

        // Attachments
        if (count($this->file_attachments)) {
            foreach ($this->file_attachments as $filename) {
                if (file_exists($filename))
                    $email->attach($filename);
            }
        }

        return $email;

        /*if ($this->file_attachment && file_exists($this->file_attachment))
            return $this->markdown('emails/site/qa-outstanding')->subject('SafeWorksite - Outstanding QA')->attach($this->file_attachment);

        return $this->markdown('emails/site/qa-outstanding')->subject('SafeWorksite - Outstanding QA');*/
    }
}
