<?php

namespace App\Mail\Misc;

use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketAction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SupportTicketUpdated extends Mailable implements ShouldQueue {

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
        /*$file_path = public_path($this->action->attachment_url);
        if ($this->action->attachment && file_exists($file_path))
            return $this->view('emails/misc/support-ticket-updated2')->subject('SafeWorksite - Support Ticket Updated')->attach($file_path);
        //return $this->markdown('emails/misc/support-ticket-updated')->subject('SafeWorksite - Support Ticket Updated')->attach($file_path);

        //return $this->markdown('emails/misc/support-ticket-updated')->subject('SafeWorksite - Support Ticket Updated');
        return $this->view('emails/misc/support-ticket-updated2')->subject('SafeWorksite - Support Ticket Updated');*/

        $email = $this->markdown('emails/misc/support-ticket-updated2')->subject('SafeWorksite - Support Ticket Updated');
        //app('log')->debug("[".$this->ticket->id."] Ticket email \n\r");
        // Attachments
        if ($this->action->files()->count()) {
            foreach ($this->action->files() as $file) {
                //app('log')->debug($file->attachment_url."\n\r");
                //app('log')->debug(public_path($file->attachment_url)."\n\r");
                //app('log')->debug($file);
                if (file_exists(substr($file->attachment_url, 1)))
                    $email->attach(public_path($file->attachment_url));
            }
        }
        return $email;
    }
}
