<?php

namespace App\Mail\Misc;

use App\Models\Misc\Supervisor\SuperChecklist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SuperChecklistReminder extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $checklist;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SuperChecklist $checklist)
    {
        $this->checklist = $checklist;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/misc/super-checklist-reminder')->subject('SafeWorksite - Supervisor Checklist Reminder');
    }
}
