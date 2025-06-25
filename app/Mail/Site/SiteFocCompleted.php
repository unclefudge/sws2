<?php

namespace App\Mail\Site;

use App\Models\Site\SiteFoc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteFocCompleted extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $foc;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SiteFoc $foc)
    {
        $this->foc = $foc;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/site/foc-completed')->subject($this->foc->site->name . ' - FOC Requirement Items have now been Completed');
    }
}
