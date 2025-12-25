<?php

namespace App\Mail\User;

use App\Models\User\UserDoc;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserDocRejected extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $doc;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(UserDoc $doc)
    {
        $this->doc = $doc;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails/user/doc-rejected')->subject('SafeWorksite - Document Not Approved');

        if ($this->doc->attachment)
            FileBank::attachToEmail($email, "user/{$this->doc->user_id}/docs/{$this->doc->attachment}");

        return $email;
    }
}
