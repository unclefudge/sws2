<?php

namespace App\Mail\Site;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteQaOutstanding extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $file_attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file_attachment)
    {
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
            return $this->markdown('emails/site/qa-outstanding')->subject('SafeWorksite - Outstanding QA')->attach($this->file_attachment);

        return $this->markdown('emails/site/qa-outstanding')->subject('SafeWorksite - Outstanding QA');
    }
}
