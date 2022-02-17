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

    public $client_planner, $file_attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ClientPlannerEmail $client_planner, $file_attachment)
    {
        $this->client_planner = $client_planner;
        $this->file_attachment = $file_attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->file_attachment && file_exists($this->file_attachment))
            return $this->markdown('emails/site/maintenance-executive')->subject('SafeWorksite - Site Maintenance Executive Report')->attach($this->file_attachment);

        return $this->markdown('emails/client/planner')->subject($this->client_planner->subject);
    }
}
