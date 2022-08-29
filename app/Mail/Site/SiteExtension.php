<?php

namespace App\Mail\Site;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteExtension extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $file_attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $file_attachment)
    {
        $this->data = $data;
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
            return $this->markdown('emails/site/contract-extension')->subject('SafeWorksite - Contract Time Extension')->attach($this->file_attachment);

        return $this->markdown('emails/site/contract-extension')->subject('SafeWorksite -Contract Time Extension');
    }
}
