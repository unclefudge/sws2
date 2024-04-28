<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteSupervisorAttendanceReport extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $attendance, $supers;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attendance, $supers)
    {
        $this->attendance = $attendance;
        $this->supers = $supers;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/supervisor-attendance')->subject('SafeWorksite -Supervisor Attendance Report');
    }
}
