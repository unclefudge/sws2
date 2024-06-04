<?php

namespace App\Mail\Site;

use App\Models\Site\SitePracCompletionItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SitePracCompletionAssignedItem extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $item;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SitePracCompletionItem $item)
    {
        $this->item = $item;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/prac-completion-assigned-item')->subject('SafeWorksite - Prac Completion Notification');
    }
}
