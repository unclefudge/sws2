<?php

namespace App\Mail\Site;

use App\Models\Site\SiteNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteNoteCreated extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $note;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteNote $note)
    {
        $this->note = $note;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->note->site->name . ' (' . $this->note->category->name . ') SiteNote[#' . $this->note->site->code . '-' . $this->note->id . ']';
        //$subject = 'SafeWorksite - SiteNote for ' . $this->note->site->name . ' ('. $this->note->category->name . ')';
        $email = $this->markdown('emails/site/note-created')->subject($subject)->from($address = 'sitenote@safeworksite.com.au', $name = 'SafeWorksite');
        // Attachments
        if ($this->note->attachments()->count()) {
            foreach ($this->note->attachments() as $attachment) {
                if (file_exists(public_path($attachment->url)))
                    $email->attach(public_path($attachment->url));
            }
        }
        return $email;

    }
}
