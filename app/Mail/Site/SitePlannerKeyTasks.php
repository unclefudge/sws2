<?php

namespace App\Mail\Site;

use App\Models\Site\Planner\SitePlanner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SitePlannerKeyTasks extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $tasks;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/planner-key-tasks')->subject('SafeWorksite - Site Planner Key Tasks');
    }
}
