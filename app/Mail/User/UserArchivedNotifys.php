<?php

namespace App\Mail\User;

use App\User;
use App\Models\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserArchivedNotifys extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user, $updated_by, $notifys;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, User $updated_by, $notifys)
    {
        $this->user = $user;
        $this->updated_by = $updated_by;
        $this->notifys = $notifys;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/user/archived-notifys')->subject('SafeWorksite - Archived user');
    }
}
