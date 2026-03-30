<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Site\Site;
use App\Services\Hia\HiaContractMapper;
use App\Services\Hia\HiaContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HiaContractController extends Controller
{
    public function listContracts(HiaContractService $hia): JsonResponse|null
    {
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        $contracts = $hia->listContractsSummary();
        ray($contracts);

        foreach ($contracts as $contract) {
            echo "Contract ID: " . $contract['contract_id'] . "<br>";
            echo "Job: " . $contract['job_number'] . "<br>";
            echo "Client: " . $contract['client'] . "<br>";
            echo "Updated: " . $contract['modified'] . "<br><br>";
            echo "<a href='/hia/contract/" . $contract['contract_id'] . "/pdf' target='_blank'>PDF</a><br><br>";

        }
        return null;
        return response()->json(['count' => count($contracts), 'data' => $contracts,]);
    }

    public function createTest(HiaContractService $hia): JsonResponse
    {
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        $contract = $hia->createContractFromTemplateAndData(9022, $this->sampleData('TEST-007', 'Sammple'));
        ray($contract);

        return response()->json($contract);
    }

    public function updateFromSite(HiaContractService $hia, HiaContractMapper $mapper, int $contractId, int $siteId): JsonResponse
    {
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        $site = Site::findOrFail($siteId);
        $data = $mapper->fromSite($site);
        ray($data);
        $contract = $hia->updateContractFromData($contractId, $data);
        ray($contract);

        return response()->json($contract);
    }


    public function pdf(HiaContractService $hia, int $contractId)
    {
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        $pdf = $hia->getContractPdf($contractId);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="hia-contract-' . $contractId . '.pdf"',
        ]);
    }
}