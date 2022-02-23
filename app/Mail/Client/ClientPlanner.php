<?php

namespace App\Mail\Client;

use App\Models\Client\ClientPlannerEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientPlanner extends Mailable implements ShouldQueue {

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
        if ($this->client_planner->docs->count()) {
            foreach ($this->client_planner->docs as $doc) {
                if (file_exists(public_path($doc->attachment_url)))
                    $email->attach(public_path($doc->attachment_url));
            }
        }

        return $email;
    }
}
