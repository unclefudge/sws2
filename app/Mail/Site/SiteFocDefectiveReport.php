<?php

namespace App\Mail\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SiteFocDefectiveReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $supervisorName, public string $supervisorFirstName, public array $jobs,)
    {
    }

    public function build()
    {
        return $this
            ->from('do-not-reply@safeworksite.com.au', 'Safe Worksite')
            ->subject('Outstanding FOC Defective Inspections')
            ->view('emails.site.foc-defective-inspections');
    }
}
