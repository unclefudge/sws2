<?php

namespace App\Mail\Safety;

use App\Models\Safety\ToolboxTalk;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use URL;

class ToolboxTalkOverdue extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ToolboxTalk $toolbox)
    {
    }

    public function build(): self
    {
        $creator = $this->toolbox->createdBy;

        return $this
            ->from('do-not-reply@safeworksite.com.au', 'SafeWorksite')
            ->subject('Toolbox Talk Overdue Notification')
            ->view('emails/toolbox-overdue')
            ->with([
                'talk_id' => $this->toolbox->id,
                'talk_name' => $this->toolbox->name,
                'talk_count' => $this->toolbox->completedBy()->count().'/'.$this->toolbox->assignedTo()->count(),
                'talk_outstanding' => $this->toolbox->outstandingBySBC(),
                'talk_url' => URL::to('/').$this->toolbox->url(),
                'user_fullname' => $creator?->fullname ?? 'SafeWorksite',
                'user_company_name' => $creator?->company?->name ?? 'SafeWorksite',
            ]);
    }
}
