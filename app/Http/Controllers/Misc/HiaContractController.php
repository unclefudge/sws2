<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Site\Site;
use App\Services\Hia\HiaContractMapper;
use App\Services\Hia\HiaContractService;
use Illuminate\Http\JsonResponse;

class HiaContractController extends Controller
{
    public function listContracts(HiaContractService $hia): JsonResponse|null
    {
        $contracts = $hia->listContractsSummary();
        ray($contracts);

        foreach ($contracts as $contract) {
            echo "Contract ID: " . $contract['contract_id'] . "<br>";
            echo "Job: " . $contract['job_number'] . "<br>";
            echo "Client: " . $contract['client'] . "<br>";
            echo "Updated: " . $contract['modified'] . "<br><br>";

        }
        return null;
        return response()->json(['count' => count($contracts), 'data' => $contracts,]);
    }

    public function createTest(HiaContractService $hia): JsonResponse
    {
        $contract = $hia->createContractFromTemplateAndData(9022, $this->sampleData('TEST-007', 'Sammple'));
        ray($contract);

        return response()->json($contract);
    }

    public function updateExisting(HiaContractService $hia, int $contractId): JsonResponse
    {
        $contract = $hia->updateContractFromData($contractId, $this->sampleData('TEST-005', 'Jamie Citizen'));

        return response()->json($contract);
    }

    public function updateFromSite(HiaContractService $hia, HiaContractMapper $mapper, int $contractId, int $siteId): JsonResponse
    {
        $site = Site::findOrFail($siteId);
        $data = $mapper->fromSite($site);
        ray($data);
        $contract = $hia->updateContractFromData($contractId, $data);
        ray($contract);

        return response()->json($contract);
    }

    protected function sampleData(string $jobNumber, string $clientName): array
    {
        return [
            'job_number' => $jobNumber,
            'client' => $clientName,
            'contract_date' => '2026-03-11',

            /*
             * contractid
             * templateid
             * status
             * job_code
             * contract_date
             */

            'owner' => [
                'title' => 'Ms',
                'firstname' => 'Jamie',
                'givennames' => 'Jamie',
                'lastname' => 'Citizen',
                'fullname' => $clientName,
                'address_line1' => '50 Example Avenue',
                'suburb' => 'Canberra',
                'state' => 'ACT',
                'postcode' => '2600',
                'country' => 'Australia',
                'mobile' => '0411111111',
                'email' => 'jamie@example.com',
                /*
                 * type
                 * organisation
                 * dpid
                 * building
                 * floor
                 * line1
                 * line2
                 * pobox
                 * block
                 * lot
                 * section
                 * volume
                 * folio
                 * certificate_of_title
                 */
            ],

            'builder' => [
                'type' => 'entity',
                'organisation' => 'Dusty Road Building Pty Ltd',
                'fullname' => 'Dusty Road Building Pty Ltd',
                'address_line1' => '12 Builder Street',
                'suburb' => 'Hobart',
                'state' => 'TAS',
                'postcode' => '7000',
                'country' => 'Australia',
                'workphone' => '03 6000 0000',
                'email' => 'builder@example.com',
                'licence_number' => 'LIC-99999',
                'abn' => '12 345 678 901',
                'acn' => '123 456 789',
            ],

            'site' => [
                'line1' => '88 Project Road',
                'suburb' => 'Canberra',
                'state' => 'ACT',
                'postcode' => '2601',
                'country' => 'Australia',
            ],
        ];
    }

    public function pdf(HiaContractService $hia, int $contractId)
    {
        $pdf = $hia->getContractPdf($contractId);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="hia-contract-' . $contractId . '.pdf"',
        ]);
    }
}