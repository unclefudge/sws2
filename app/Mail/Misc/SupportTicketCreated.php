<?php

namespace App\Mail\Misc;

use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketAction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SupportTicketCreated extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $ticket;
    public $action;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SupportTicket $ticket, SupportTicketAction $action)
    {
        $this->ticket = $ticket;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails/misc/support-ticket-created2')->subject('SafeWorksite - New Support Ticket');
        // Attachments
        if ($this->action->files()->count()) {
            foreach ($this->action->files() as $file) {
                if (file_exists(substr($file->attachment_url, 1)))
                    $email->attach(public_path($file->attachment_url));
            }
        }
        return $email;
    }
}
