<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteTradesAttendance extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $file_attachments, $non_attendance;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file_attachments, $non_attendance)
    {
        $this->file_attachments = $file_attachments;
        $this->non_attendance = $non_attendance;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails/site/trades-attendance')->subject('SafeWorksite - Monthly Trades Attendance');

        // Attachments
        if (count($this->file_attachments)) {
            foreach ($this->file_attachments as $filename) {
                if (file_exists($filename))
                    $email->attach($filename);
            }
        }

        return $email;
    }
}
