<?php

namespace App\Mail\Site;

use App\Models\Site\SiteProjectSupply;
use App\Services\FileBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteProjectSupplyCompleted extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $project;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteProjectSupply $project)
    {
        $this->project = $project;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $email = $this->markdown('emails/site/project-supply-completed')->subject('SafeWorksite - Project Supply Completed');

        if ($this->project->attachment) {
            $path = "site/{$this->project->site_id}/docs/{$this->project->attachment}";

            FileBank::attachToEmail($email, $path);
        }

        return $email;
    }
}
