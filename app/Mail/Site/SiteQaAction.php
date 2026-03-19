<?php

namespace App\Mail\Site;

use App\Models\Misc\Action;
use App\Models\Site\SiteQa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteQaAction extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $qa;
    public $action;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteQa $qa, Action $action)
    {
        $this->qa = $qa;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/qa-action')->subject('SafeWorksite - QA Notification');
    }
}
