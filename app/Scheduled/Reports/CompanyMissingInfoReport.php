<?php

namespace App\Scheduled\Reports;

use App\Mail\Company\CompanyMissingInfo;
use App\Models\Company\Company;
use App\Scheduled\Contracts\ScheduledOperationHandler;

class CompanyMissingInfoReport implements ScheduledOperationHandler
{
    /**
     * This legacy report was disabled before conversion. Installing the
     * discovered handler creates it disabled so it can be reviewed first.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.missing_company_info',
            'name' => 'Missing company information',
            'category' => 'report',
            'description' => 'Emails missing company information and required document details for active Cape Cod companies.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Notification group: company.missing.info',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Build the missing-information and document-category lists, then email the report.
     */
    public function handle(): int
    {
        $capeCod = Company::findOrFail(3);
        $companies = $capeCod->companies(1)->sortBy('name');
        $missingInformation = [];
        $expiredDocuments1 = [];
        $expiredDocuments2 = [];
        $expiredDocuments3 = [];

        foreach ($companies as $company) {
            // Fake/internal companies prefixed with cc- are deliberately excluded.
            if (preg_match('/cc-/', strtolower($company->name))) continue;

            $missingInfo = $company->missingInfo();
            if ($missingInfo) {
                $missingInformation[] = [
                    'id' => $company->id,
                    'company_name' => $company->name,
                    'company_nickname' => $company->nickname ? "<span class='font-grey-cascade'><br>{$company->nickname}</span>" : '',
                    'data' => $missingInfo,
                    'date' => $company->updated_at->format('d/m/Y'),
                    'link' => "/company/{$company->id}",
                ];
            }

            if (!$company->isMissingDocs()) continue;

            foreach ($company->missingDocs() as $type => $name) {
                $entry = $this->documentEntry($company, (int)$type, $name);

                // Document groups are retained because the existing mailable
                // displays these categories in separate sections.
                if (in_array((int)$type, [1, 2, 3, 7, 12], true)) $expiredDocuments1[] = $entry;
                elseif (in_array((int)$type, [4, 5], true)) $expiredDocuments2[] = $entry;
                elseif ((int)$type === 6) $expiredDocuments3[] = $entry;
            }
        }

        echo 'Active companies checked: ' . $companies->count() . "\n";
        echo 'Missing information entries: ' . count($missingInformation) . "\n";
        echo 'Missing or expired document entries: ' . (count($expiredDocuments1) + count($expiredDocuments2) + count($expiredDocuments3)) . "\n";

        // The legacy method always sent the report, including when all lists were empty.
        $emailList = $capeCod->notificationsUsersEmailType('company.missing.info');
        $mailable = new CompanyMissingInfo($companies, $missingInformation, $expiredDocuments1, $expiredDocuments2, $expiredDocuments3);
        if ($emailList) $mailable->to($emailList);
        $mailable->send(app('mailer'));

        echo "Missing Company Information report sent.\n";

        return 1;
    }

    private function documentEntry(Company $company, int $type, string $name): array
    {
        $doc = $company->expiredCompanyDoc($type);
        $hasDocument = $doc && $doc !== 'N/A';

        return [
            'id' => $company->id,
            'company_name' => $company->name,
            'company_nickname' => $company->nickname ? "<span class='font-grey-cascade'><br>{$company->nickname}</span>" : '',
            'data' => $name,
            'date' => $hasDocument && $doc->expiry ? $doc->expiry->format('d/m/Y') : 'never',
            'link' => $hasDocument ? "/company/{$company->id}/doc/{$doc->id}/edit" : "/company/{$company->id}/doc",
        ];
    }
}
