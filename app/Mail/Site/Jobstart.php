<?php

namespace App\Mail\Site;

use App\Models\Site\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Jobstart extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $site, $newdate, $olddate, $supers;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Site $site, $newdate, $olddate = null, $supers)
    {
        $this->site = $site;
        $this->supers = $supers;
        $this->newdate = $newdate;
        $this->olddate = $olddate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/jobstart')->subject('SafeWorksite - Job Start Notification');
    }
}
