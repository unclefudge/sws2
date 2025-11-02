<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteUpcomingJobs extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $file_attachment, $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file_attachment, $subject)
    {
        $this->subject = $subject;
        $this->file_attachment = $file_attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->file_attachment && file_exists($this->file_attachment))
            return $this->markdown('emails/site/upcoming-jobs')->subject($this->subject)->attach($this->file_attachment);

        return $this->markdown('emails/site/upcoming-jobs')->subject($this->subject);
    }
}
