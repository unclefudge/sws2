<?php

namespace App\Services\Hia;

use RuntimeException;

class HiaContractService
{
    public function __construct(protected HiaApiService $api, protected HiaContractXmlService $xmlService,)
    {
    }

    public function createContractFromTemplateAndData(int $dataTemplateId, array $data): array
    {
        $contractId = $this->api->createContract($dataTemplateId);

        return $this->updateContractFromData($contractId, $data);
    }

    public function updateContractFromData(int $contractId, array $data): array
    {
        $contract = $this->api->getContractById($contractId);

        return $this->updateFetchedContract($contract, $data);
    }

    public function updateFetchedContract(array $contract, array $data): array
    {
        $contractId = (int)($contract['ContractId'] ?? 0);

        if ($contractId <= 0) {
            throw new RuntimeException('ContractId missing from fetched contract.');
        }

        $source = $contract['Source'] ?? null;

        if (blank($source)) {
            throw new RuntimeException("Contract {$contractId} has no Source XML to update.");
        }

        $data['contract_id'] = $contractId;
        $data['template_id'] = (int)($contract['TemplateId'] ?? 0);
        $data['status'] = (int)($contract['Status'] ?? 1);

        $xml = $this->xmlService->load($source);
        $xml = $this->xmlService->apply($xml, $data);

        $contract['Source'] = $this->xmlService->toString($xml);

        if (isset($data['job_number'])) {
            $contract['JobNumber'] = $data['job_number'];
        }

        if (isset($data['client'])) {
            $contract['Client'] = $data['client'];
        } elseif (!empty($data['owner']['fullname'])) {
            $contract['Client'] = $data['owner']['fullname'];
        }

        if (isset($data['contract_date'])) {
            $contract['LastModifiedDate'] = now()->toIso8601String();
        }

        $this->api->updateContract($contract);

        return $this->api->getContractById($contractId);
    }

    public function listContractsSummary(): array
    {
        $contracts = $this->api->getContracts(); // raw list from HIA

        return collect($contracts)->map(function ($c) {
            return [
                'contract_id' => $c['ContractId'] ?? null,
                'template_id' => $c['TemplateId'] ?? null,
                'job_number' => $c['JobNumber'] ?? null,
                'client' => $c['Client'] ?? null,
                'status' => $c['Status'] ?? null,
                'modified' => $c['LastModifiedDate'] ?? null,
            ];
        })->values()->all();
    }

    public function getContractById(int $contractId): array
    {
        return $this->api->getContractById($contractId);
    }

    public function getDataTemplates(): array
    {
        return $this->api->getDataTemplates();
    }

    public function getDataTemplateById(int $dataTemplateId): array
    {
        return $this->api->getDataTemplateById($dataTemplateId);
    }

    public function getContractPdf(int $contractId): string
    {
        return $this->api->getContractPdf($contractId);
    }
}