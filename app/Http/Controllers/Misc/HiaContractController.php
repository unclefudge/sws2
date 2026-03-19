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

    public function updateFromSite(HiaContractService $hia, HiaContractMapper $mapper, int $contractId, int $siteId): JsonResponse
    {
        $site = Site::findOrFail($siteId);
        $data = $mapper->fromSite($site);
        ray($data);
        $contract = $hia->updateContractFromData($contractId, $data);
        ray($contract);

        return response()->json($contract);
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