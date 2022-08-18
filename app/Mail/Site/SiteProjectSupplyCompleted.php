<?php

namespace App\Mail\Site;

use App\Models\Site\SiteProjectSupply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteProjectSupplyCompleted extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $project, $file_attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteProjectSupply $project, $file_attachment)
    {
        $this->project = $project;
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
            return $this->markdown('emails/site/project-supply-completed')->subject('SafeWorksite - Project Supply Completed')->attach($this->file_attachment);

        return $this->markdown('emails/site/project-supply-completed')->subject('SafeWorksite - Project Supply Completed');
    }
}
