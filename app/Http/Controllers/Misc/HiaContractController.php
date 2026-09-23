<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Site\SiteContract;
use App\Services\Hia\HiaContractMapper;
use App\Services\Hia\HiaContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class HiaContractController extends Controller
{
    private const COMPANY_ID = 3;
    private const DEFAULT_TEMPLATE_ID = 9022;

    /**
     * Display a combined list of SafeWorksite and live HIA contracts.
     */
    public function index(HiaContractService $hia): View
    {
        $this->authorise();

        $localContracts = SiteContract::query()
            ->with('site:id,company_id,code,name,address,suburb,state,postcode')
            ->whereHas('site', fn ($query) => $query->where('company_id', self::COMPANY_ID))
            ->orderByDesc('updated_at')
            ->get();

        $hiaContracts = [];
        $hiaError = null;

        try {
            $hiaContracts = $hia->listContractsSummary();
        } catch (Throwable $e) {
            $hiaError = $e->getMessage();
            Log::error('Unable to load the HIA contract management list', ['message' => $e->getMessage()]);
        }

        return view('manage/hia/contracts/list', [
            'contracts' => $this->mergeContracts($localContracts, $hiaContracts, $hiaError !== null),
            'hiaError' => $hiaError,
        ]);
    }

    /**
     * Show local contract data alongside the linked live HIA contract.
     */
    public function show(SiteContract $siteContract, HiaContractService $hia): View
    {
        $this->authorise();
        $this->guardCapeCodContract($siteContract);

        $siteContract->load('site');
        $hiaContract = null;
        $hiaError = null;

        if ($siteContract->hia_contract_id) {
            try {
                $hiaContract = $hia->getContractById((int) $siteContract->hia_contract_id);
            } catch (Throwable $e) {
                $hiaError = $e->getMessage();
                Log::error('Unable to load an HIA contract', [
                    'site_contract_id' => $siteContract->id,
                    'hia_contract_id' => $siteContract->hia_contract_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return view('manage/hia/contracts/view', [
            'contract' => $siteContract,
            'hiaContract' => $hiaContract,
            'hiaError' => $hiaError,
            'comparison' => $this->comparison($siteContract, $hiaContract),
            'hiaJson' => $hiaContract ? json_encode($hiaContract, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '',
        ]);
    }

    /**
     * Create or update the HIA contract using current SafeWorksite data.
     */
    public function sync(SiteContract $siteContract, HiaContractService $hia, HiaContractMapper $mapper): RedirectResponse
    {
        $this->authorise();
        $this->guardCapeCodContract($siteContract);

        $siteContract->load('site');

        try {
            $hiaData = $mapper->fromSite($siteContract->site);
            $wasExisting = filled($siteContract->hia_contract_id);

            $hiaContract = $wasExisting
                ? $hia->updateContractFromData((int) $siteContract->hia_contract_id, $hiaData)
                : $hia->createContractFromTemplateAndData(self::DEFAULT_TEMPLATE_ID, $hiaData);

            $contractId = (int) ($hiaContract['ContractId'] ?? 0);

            if ($contractId <= 0) {
                throw new \RuntimeException('HIA returned no ContractId after synchronisation.');
            }

            $pdfPath = "site/{$siteContract->site_id}/contracts/hia-contract-{$contractId}.pdf";
            Storage::disk('local')->put($pdfPath, $hia->getContractPdf($contractId));

            $siteContract->update([
                'hia_contract_id' => $contractId,
                'hia_template_id' => $hiaContract['TemplateId'] ?? self::DEFAULT_TEMPLATE_ID,
                'hia_xml' => $hiaContract['Source'] ?? null,
                'hia_pdf' => $pdfPath,
            ]);

            Log::info('HIA contract manually synchronised', [
                'user_id' => Auth::id(),
                'site_contract_id' => $siteContract->id,
                'site_id' => $siteContract->site_id,
                'hia_contract_id' => $contractId,
                'action' => $wasExisting ? 'updated' : 'created',
            ]);

            return redirect()->route('hia.contracts.show', $siteContract)
                ->with('success', 'HIA contract ' . ($wasExisting ? 'updated' : 'created') . ' successfully.');
        } catch (Throwable $e) {
            Log::error('Manual HIA contract synchronisation failed', [
                'user_id' => Auth::id(),
                'site_contract_id' => $siteContract->id,
                'site_id' => $siteContract->site_id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('hia.contracts.show', $siteContract)
                ->with('error', 'HIA synchronisation failed: ' . $e->getMessage());
        }
    }

    /**
     * Stream the current PDF directly from HIA.
     */
    public function pdf(HiaContractService $hia, int $contractId)
    {
        $this->authorise();

        $pdf = $hia->getContractPdf($contractId);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="hia-contract-' . $contractId . '.pdf"',
        ]);
    }

    /**
     * Stream the last PDF saved to SafeWorksite during synchronisation.
     */
    public function storedPdf(SiteContract $siteContract)
    {
        $this->authorise();
        $this->guardCapeCodContract($siteContract);

        if (!$siteContract->hia_pdf || !Storage::disk('local')->exists($siteContract->hia_pdf)) {
            abort(404, 'Stored HIA PDF not found.');
        }

        return Storage::disk('local')->response(
            $siteContract->hia_pdf,
            basename($siteContract->hia_pdf),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    /**
     * Lightweight API check retained for development/testing.
     */
    public function listContracts(HiaContractService $hia): JsonResponse
    {
        $this->authorise();

        $contracts = $hia->listContractsSummary();

        return response()->json(['count' => count($contracts), 'data' => $contracts]);
    }

    protected function mergeContracts($localContracts, array $hiaContracts, bool $hiaUnavailable = false): array
    {
        $hiaById = collect($hiaContracts)->keyBy(fn ($contract) => (string) ($contract['contract_id'] ?? ''));
        $localHiaIds = $localContracts->pluck('hia_contract_id')->filter()->map(fn ($id) => (string) $id);
        $possibleHiaIds = collect();
        $rows = [];

        foreach ($localContracts as $local) {
            $hia = $local->hia_contract_id ? $hiaById->get((string) $local->hia_contract_id) : null;
            $possibleMatch = null;

            if (!$local->hia_contract_id && $local->site?->code) {
                $possibleMatch = collect($hiaContracts)->first(
                    fn ($contract) => strcasecmp(trim((string) ($contract['job_number'] ?? '')), trim((string) $local->site->code)) === 0
                );

                if ($possibleMatch && !empty($possibleMatch['contract_id'])) {
                    $possibleHiaIds->push((string) $possibleMatch['contract_id']);
                }
            }

            $state = $hiaUnavailable ? 'hia_unavailable' : ($hia ? 'matched' : ($possibleMatch ? 'possible_match' : 'sws_only'));

            if (!$hiaUnavailable && $local->hia_contract_id && !$hia) {
                $state = 'missing_hia';
            }

            $rows[] = ['local' => $local, 'hia' => $hia, 'possible_match' => $possibleMatch, 'state' => $state];
        }

        foreach ($hiaContracts as $hia) {
            $hiaId = (string) ($hia['contract_id'] ?? '');

            if ($hiaId !== '' && !$localHiaIds->contains($hiaId) && !$possibleHiaIds->contains($hiaId)) {
                $rows[] = ['local' => null, 'hia' => $hia, 'possible_match' => null, 'state' => 'hia_only'];
            }
        }

        return $rows;
    }

    protected function comparison(SiteContract $contract, ?array $hiaContract): array
    {
        $site = $contract->site;

        return [
            ['label' => 'Contract ID', 'sws' => $contract->hia_contract_id, 'hia' => $hiaContract['ContractId'] ?? null],
            ['label' => 'Template ID', 'sws' => $contract->hia_template_id, 'hia' => $hiaContract['TemplateId'] ?? null],
            ['label' => 'Job Number', 'sws' => $site?->code, 'hia' => $hiaContract['JobNumber'] ?? null],
            ['label' => 'Client', 'sws' => $site?->name, 'hia' => $hiaContract['Client'] ?? null],
            ['label' => 'Owner', 'sws' => $contract->owner1_name, 'hia' => $hiaContract['Client'] ?? null],
            ['label' => 'HIA Status', 'sws' => null, 'hia' => $hiaContract['Status'] ?? null],
        ];
    }

    protected function guardCapeCodContract(SiteContract $siteContract): void
    {
        $siteContract->loadMissing('site');

        if (!$siteContract->site || (int) $siteContract->site->company_id !== self::COMPANY_ID) {
            abort(404);
        }
    }

    protected function authorise(): void
    {
        if (!Auth::check() || !Auth::user()->hasAnyRole2('web-admin|mgt-general-manager')) {
            abort(404);
        }
    }
}
