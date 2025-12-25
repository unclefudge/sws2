<?php

namespace App\Mail\Misc;

use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketAction;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketCreated extends Mailable implements ShouldQueue
{

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

        // Add Attachments
        foreach ($this->ticket->attachments as $file) {
            if ($file->directory && $file->attachment) {
                FileBank::attachToEmail($email, "$file->directory/$file->attachment");
            }
        }

        return $email;
    }
}
