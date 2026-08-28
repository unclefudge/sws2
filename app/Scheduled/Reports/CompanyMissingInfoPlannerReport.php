<?php

namespace App\Scheduled\Reports;

use App\Mail\Company\CompanyMissingInfoPlanner;
use App\Models\Company\Company;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;

class CompanyMissingInfoPlannerReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.missing_company_info_planner',
            'name' => 'Planned companies missing information',
            'category' => 'report',
            'description' => 'Emails missing information and expired document details for active companies with upcoming planner work.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05'], // Monday
            'recipients' => 'Legacy notification group: company.missing.info; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find planned companies with missing information or documents and email the report.
     */
    public function handle(): int
    {
        $plannedCompanies = Company::query()->where('parent_company', 3)->where('status', 1)->get()
            ->map(function (Company $company) {
                $plannerDate = $company->nextDateOnPlanner();
                return $plannerDate ? ['company' => $company, 'planner_date' => $plannerDate] : null;
            })->filter()->sortBy(fn(array $plannedCompany) => $plannedCompany['planner_date']->getTimestamp())->values();

        echo "Active companies with upcoming planner work: {$plannedCompanies->count()}\n";

        $dayAgo = Carbon::today()->subDay();
        $missing = [];

        foreach ($plannedCompanies as $plannedCompany) {
            /** @var Company $company */
            $company = $plannedCompany['company'];
            $plannerDate = $plannedCompany['planner_date'];

            if (preg_match('/cc-/', strtolower($company->name))) continue;

            $missingInfo = $company->missingInfo();
            if (!$missingInfo && !$company->isMissingDocs()) continue;

            $missingDocs = [];
            foreach ($company->missingDocs() as $type => $name) {
                $doc = $company->expiredCompanyDoc($type);
                if (!$doc || ($doc !== 'N/A' && $doc->expiry && !$doc->expiry->lt($dayAgo))) continue;

                $hasDocument = $doc !== 'N/A';
                $missingDocs[] = [
                    'name' => $name,
                    'link' => $hasDocument ? "<a href='/company/{$company->id}/doc/{$doc->id}/edit'>{$name}</a>" : "<a href='/company/{$company->id}/doc'>{$name}</a>",
                    'expiry_human' => $hasDocument && $doc->expiry ? $doc->expiry->longAbsoluteDiffForHumans() : 'never',
                    'expiry_date' => $hasDocument && $doc->expiry ? $doc->expiry->format('d/m/Y') : '-',
                ];
            }

            $missing[] = [
                'company_name' => $company->name,
                'company_nickname' => $company->nickname ? "({$company->nickname})" : '',
                'next_planner' => $plannerDate->longAbsoluteDiffForHumans(),
                'missing_info' => $missingInfo ? $missingInfo . '<br>' : '',
                'docs' => $missingDocs,
            ];
        }

        echo 'Planned companies missing information or documents: ' . count($missing) . "\n";

        // This remains the legacy/default recipient source. The central mail
        // listener can append dashboard rules or replace this list entirely.
        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('company.missing.info');
        $mailable = new CompanyMissingInfoPlanner($missing);
        if ($emailList) $mailable->to($emailList);
        $mailable->send(app('mailer'));

        echo "Planned Companies Missing Information report sent.\n";

        return 1;
    }
}
