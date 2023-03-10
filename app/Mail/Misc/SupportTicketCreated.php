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
        $file_path = public_path($this->action->attachment_url);
        if ($this->action->attachment && file_exists($file_path))
            return $this->view('emails/misc/support-ticket-created2')->subject('SafeWorksite - New Support Ticket')->attach($file_path);
        //return $this->markdown('emails/misc/support-ticket-created')->subject('SafeWorksite - New Support Ticket')->attach($file_path);

        //return $this->markdown('emails/misc/support-ticket-created')->subject('SafeWorksite - New Support Ticket');
        return $this->view('emails/misc/support-ticket-created2')->subject('SafeWorksite - New Support Ticket');
    }
}
