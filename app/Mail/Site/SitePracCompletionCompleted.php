<?php

namespace App\Mail\Site;

use App\Models\Site\SitePracCompletion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SitePracCompletionCompleted extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $prac;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SitePracCompletion $prac)
    {
        $this->prac = $prac;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/prac-completion-completed')->subject('SafeWorksite - Prac Completion completed');
    }
}
