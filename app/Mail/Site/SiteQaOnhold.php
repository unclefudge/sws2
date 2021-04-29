<?php

namespace App\Mail\Site;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteQaOnhold extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $file_attachment, $qas;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file_attachment, $qas)
    {
        $this->file_attachment = $file_attachment;
        $this->qas = $qas;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->file_attachment && file_exists($this->file_attachment))
            return $this->markdown('emails/site/qa-onhold')->subject('SafeWorksite - On Hold QA')->attach($this->file_attachment);

        return $this->markdown('emails/site/qa-onhold')->subject('SafeWorksite - On Hold QA');
    }
}
