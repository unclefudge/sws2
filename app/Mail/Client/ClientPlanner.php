<?php

namespace App\Mail\Client;

use App\Models\Client\ClientPlannerEmail;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientPlanner extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $client_planner;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ClientPlannerEmail $client_planner)
    {
        $this->client_planner = $client_planner;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails/client/planner')->subject($this->client_planner->subject);

        // Attachments

        foreach ($this->client_planner->attachments as $file) {
            if ($file->directory && $file->attachment)
                FileBank::attachToEmail($email, "$file->directory/$file->attachment}");
        }

        return $email;
    }
}
