<?php

namespace App\Mail\Safety;

use App\Models\Safety\ToolboxTalk;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ToolboxTalkUsers extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $talk, $added, $deleted;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ToolboxTalk $talk, $added, $deleted)
    {
        $this->talk = $talk;
        $this->added = $added;
        $this->deleted = $deleted;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/safety/toolbox-users')->subject('SafeWorksite - Toolbox Talk Users');
    }
}
