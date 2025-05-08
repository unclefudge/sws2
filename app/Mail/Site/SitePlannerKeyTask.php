<?php

namespace App\Mail\Site;

use App\Models\Site\Planner\SitePlanner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SitePlannerKeyTask extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $task, $subject, $mesg;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SitePlanner $task, $subject, $mesg)
    {
        $this->task = $task;
        $this->subject = $subject;
        $this->mesg = $mesg;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/planner-key-task')->subject($this->subject);
    }
}
