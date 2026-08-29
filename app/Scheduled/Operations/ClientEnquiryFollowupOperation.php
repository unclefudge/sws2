<?php

namespace App\Scheduled\Operations;

use App\Models\Misc\WebsiteFormSubmission;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledReportMailer;
use Carbon\Carbon;

class ClientEnquiryFollowupOperation implements ScheduledOperationHandler
{
    public function __construct(private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'hourly.client_enquiry_followup',
            'name' => 'Client enquiry follow-up',
            'category' => 'hourly',
            'description' => 'Expires incomplete client enquiries after one day and emails an internal follow-up for valid enquiries aged between two and twenty-four hours.',
            'schedule' => ['type' => 'hourly', 'minute' => 1],
            'recipients' => 'Dashboard-configurable internal follow-up recipients',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $now = Carbon::now();
        $followUpBefore = $now->copy()->subHours(2);
        $expireBefore = $now->copy()->subDay();

        $expired = WebsiteFormSubmission::query()->where('status', 'step1 complete')->where('created_at', '<', $expireBefore)->update(['status' => 'step1 expired']);
        $enquiries = WebsiteFormSubmission::query()->where('status', 'step1 complete')->whereBetween('created_at', [$expireBefore, $followUpBefore])->orderBy('id')->get();
        $sent = 0;
        $invalid = 0;

        foreach ($enquiries as $enquiry) {
            if ($enquiry->email && validEmail($enquiry->email)) {
                $this->mailer->send(new \App\Mail\Misc\ClientEnquiryFollowup($enquiry));
                $enquiry->status = 'step1 followup';
                $sent++;
            } else {
                $enquiry->status = 'invalid email';
                $invalid++;
            }

            $enquiry->save();
        }

        echo "Expired enquiries: {$expired}\n";
        echo "Follow-up emails sent: {$sent}\n";
        echo "Invalid enquiry emails: {$invalid}\n";

        return $sent;
    }
}
