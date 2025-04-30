<?php

namespace App\Mail\Site;

use App\Models\Site\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteSync extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $site, $zoho, $diff;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Site $site, $zoho, $diff)
    {
        $this->site = $site;
        $this->zoho = $zoho;
        $this->diff = $diff;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/sitesync')->subject('SafeWorksite - Zoho Site Sync');
    }
}
