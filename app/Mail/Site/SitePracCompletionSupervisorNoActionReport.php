<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SitePracCompletionSupervisorNoActionReport extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($body)
    {
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //if ($this->file_attachment && file_exists($this->file_attachment))
        //    $this->markdown('emails/site/prac-completion-supervisor-noaction')->subject('SafeWorksite - Prac Completion No Actions')->attach($this->file_attachment);

        return $this->markdown('emails/site/prac-completion-supervisor-noaction')->subject('SafeWorksite - Prac Completion No Actions');
    }
}
