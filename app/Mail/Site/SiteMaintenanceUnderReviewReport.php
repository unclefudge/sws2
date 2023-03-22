<?php

namespace App\Mail\Site;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteMaintenanceUnderReviewReport extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $file_attachment, $mains;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file_attachment, $mains)
    {
        $this->file_attachment = $file_attachment;
        $this->mains = $mains;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->file_attachment && file_exists($this->file_attachment))
            return $this->markdown('emails/site/maintenance-under-review')->subject('SafeWorksite - Maintenance Under Review')->attach($this->file_attachment);

        return $this->markdown('emails/site/maintenance-under-review')->subject('SafeWorksite - Maintenance Under Review');
    }
}
