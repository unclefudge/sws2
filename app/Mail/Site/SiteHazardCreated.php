<?php

namespace App\Mail\Site;

use App\Models\Misc\Action;
use App\Models\Site\SiteHazard;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteHazardCreated extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $hazard;
    public $action;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteHazard $hazard, Action $action)
    {
        $this->hazard = $hazard;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails/site/hazard-created')->subject('SafeWorksite - Hazard Notification');

        // Add Attachments
        foreach ($this->hazard->attachments as $file) {
            if ($file->directory && $file->attachment) {
                FileBank::attachToEmail($email, "$file->directory/$file->attachment");
            }
        }

        return $email;
    }
}
