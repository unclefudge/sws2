<?php

namespace App\Mail\Site;

use App\Models\Site\SiteNote;
use App\Services\FileBank;
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
        $email = $this->markdown('emails/site/note-created')->subject($subject)->from($address = 'sitenote@safeworksite.com.au', $name = 'SafeWorksite');

        // Add Attachments
        foreach ($this->note->attachments as $file) {
            if ($file->directory && $file->attachment) {
                FileBank::attachToEmail($email, "$file->directory/$file->attachment");
            }
        }
        return $email;

    }
}
