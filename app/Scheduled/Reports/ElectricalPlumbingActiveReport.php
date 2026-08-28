<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteInspectionActive;
use App\Models\Company\Company;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledReportMailer;
use Illuminate\Support\Collection;

class ElectricalPlumbingActiveReport implements ScheduledOperationHandler
{
    public function __construct(private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.active_electrical_plumbing',
            'name' => 'Active electrical and plumbing inspections',
            'category' => 'report',
            'description' => 'Emails each assigned company its active electrical or plumbing inspections.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [4], 'time' => '00:05'], // Thursday
            'recipients' => 'Assigned inspection company (To) and legacy site.inspection.open group (CC); dashboard recipients can append to or replace the legacy group',
            'dynamicRecipients' => [
                ['key' => 'assigned_company', 'label' => 'Assigned inspection company', 'delivery' => 'to', 'description' => 'The company assigned to the inspections contained in the individual email.', 'required' => true],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $electrical = SiteInspectionElectrical::query()->where('status', 1)->get();
        $plumbing = SiteInspectionPlumbing::query()->where('status', 1)->get();
        $companyIds = $electrical->pluck('assigned_to')->merge($plumbing->pluck('assigned_to'))->filter()->unique();
        $companies = Company::query()->whereIn('id', $companyIds)->get()->keyBy('id');
        $managementEmails = Company::findOrFail(3)->notificationsUsersEmailType('site.inspection.open');

        echo 'Active electrical inspections: ' . $electrical->count() . "\n";
        echo 'Active plumbing inspections: ' . $plumbing->count() . "\n";

        $emailsSent = $this->sendCompanyReports($electrical->groupBy(fn($inspection) => (int)($inspection->assigned_to ?: 0)), $companies, $electrical, $plumbing, 'Electrical', $managementEmails);
        $emailsSent += $this->sendCompanyReports($plumbing->groupBy(fn($inspection) => (int)($inspection->assigned_to ?: 0)), $companies, $electrical, $plumbing, 'Plumbing', $managementEmails);

        if (!$emailsSent) echo "No email required.\n";

        return $emailsSent;
    }

    /**
     * One email is sent for each inspection type/company combination so an
     * assigned contractor receives only the inspections belonging to them.
     */
    private function sendCompanyReports(Collection $groups, Collection $companies, Collection $allElectrical, Collection $allPlumbing, string $type, array $managementEmails): int
    {
        $emailsSent = 0;

        foreach ($groups as $companyId => $inspections) {
            $company = $companyId ? $companies->get($companyId) : null;
            $dynamicRecipients = $this->assignedCompanyRecipients($company, $type);
            $hasAssignedCompany = collect($dynamicRecipients)->contains(fn($recipient) => !empty($recipient['email']));
            $electrical = $type === 'Electrical' ? $inspections : $allElectrical;
            $plumbing = $type === 'Plumbing' ? $inspections : $allPlumbing;
            $mailable = new SiteInspectionActive($electrical, $plumbing, $type);

            // Preserve the old fallback: management receives the email as To
            // when the assigned company has no valid address, otherwise as CC.
            if ($managementEmails) {
                if ($hasAssignedCompany) $mailable->cc($managementEmails);
                else $mailable->to($managementEmails);
            }

            $this->mailer->send($mailable, $dynamicRecipients);
            $companyName = $company?->name ?: 'Unassigned company';
            echo "{$type} inspection report sent for {$companyName}: " . $inspections->count() . " inspection(s).\n";
            $emailsSent++;
        }

        return $emailsSent;
    }

    /**
     * The legacy report used the company's general email. Plumbing company 69
     * also received a copy at its primary contact, so that exception remains.
     */
    private function assignedCompanyRecipients(?Company $company, string $type): array
    {
        if (!$company || !filter_var($company->email, FILTER_VALIDATE_EMAIL)) {
            return [['key' => 'assigned_company', 'label' => 'Assigned inspection company', 'type' => 'to', 'email' => null, 'name' => null, 'required' => true, 'reason' => 'The assigned company has no valid general email address.']];
        }

        $recipients = [['key' => 'assigned_company', 'label' => 'Assigned inspection company', 'type' => 'to', 'email' => $company->email, 'name' => $company->name, 'required' => true, 'reason' => null]];

        if ($type === 'Plumbing' && (int)$company->id === 69) {
            $primaryContact = $company->primary_contact();
            if ($primaryContact && filter_var($primaryContact->email, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = ['key' => 'assigned_company', 'label' => 'Assigned inspection company primary contact', 'type' => 'to', 'email' => $primaryContact->email, 'name' => $primaryContact->fullname, 'required' => true, 'reason' => null];
            }
        }

        return $recipients;
    }
}
