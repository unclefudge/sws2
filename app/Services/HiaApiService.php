<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

class HiaApiService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $clientId;
    protected string $clientSecret;
    protected string $apiVersion;
    protected string $scope;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.hia.base_url'), '/');
        $this->username = config('services.hia.username');
        $this->password = config('services.hia.password');
        $this->clientId = config('services.hia.client_id');
        $this->clientSecret = config('services.hia.client_secret');
        $this->apiVersion = config('services.hia.api_version', '1.0.0');
        $this->scope = config('services.hia.scope', 'BASIC,COL');
    }

    /**
     * Get cached access token.
     */
    public function token(): string
    {
        /*dd([
            'base_url' => $this->baseUrl,
            'username' => $this->username,
            'password_set' => filled($this->password),
            'client_id' => $this->clientId,
            'client_secret_set' => filled($this->clientSecret),
            'api_version' => $this->apiVersion,
            'scope' => $this->scope,
        ]);*/
        return Cache::remember('hia_access_token', now()->addMinutes(18), function () {
            $payload = http_build_query([
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'api_version' => $this->apiVersion,
                'scope[]' => $this->scope,
                'templates_version' => '1.0.0',
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => '*/*',
            ])->withBody($payload, 'application/x-www-form-urlencoded')
                ->post($this->baseUrl . '/token');

            ray([
                'url' => $this->baseUrl . '/token',
                'payload' => $payload,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                throw new RuntimeException(
                    'HIA token request failed: ' . $response->status() . ' - ' . $response->body()
                );
            }

            $json = $response->json();

            if (!isset($json['access_token'])) {
                throw new RuntimeException('HIA token response missing access_token.');
            }

            return $json['access_token'];
        });
    }

    /**
     * Base authenticated request builder.
     */
    protected function request()
    {
        return Http::withToken($this->token())
            ->withHeaders([
                'Accept' => '*/*',
            ])
            ->baseUrl($this->baseUrl)
            ->connectTimeout(10)
            ->timeout(120);
    }

    /**
     * Get all templates.
     */
    public function getDataTemplates(): array
    {
        $response = $this->request()->get('/api/contracts/DataTemplates');

        return $this->handleJsonResponse($response);
    }

    /**
     * Get a specific template by DataTemplateId.
     */
    public function getDataTemplateById(int $dataTemplateId): array
    {
        $response = $this->request()->get("/api/contracts/DataTemplates/{$dataTemplateId}");

        return $this->handleJsonResponse($response);
    }

    /**
     * Create a blank contract from a DataTemplateId.
     * Returns the new ContractId.
     */
    public function createContract(int $dataTemplateId): int
    {
        $url = "/api/contracts/ContractInstances/{$dataTemplateId}";

        ray()->measure();
        $response = $this->request()->put($url);
        ray([
            'step' => 'createContract',
            'url' => $this->baseUrl . $url,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'HIA create contract failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        $contractId = trim($response->body());

        if (!is_numeric($contractId)) {
            throw new RuntimeException('Unexpected create contract response: ' . $response->body());
        }

        return (int)$contractId;
    }

    /**
     * Get contracts for current user.
     */
    public function getContracts(): array
    {
        $response = $this->request()->get('/api/contracts/ContractInstances');

        // This API sometimes returns a 500 for "No Contracts found"
        if ($response->status() === 500 && str_contains($response->body(), 'No Contracts found')) {
            return [];
        }

        return $this->handleJsonResponse($response);
    }

    /**
     * Get a contract by ContractId.
     */
    public function getContractById(int $contractId): array
    {
        $url = "/api/contracts/ContractInstances/{$contractId}";

        $response = $this->request()->get($url);

        ray([
            'step' => 'getContractById',
            'url' => $this->baseUrl . $url,
            'status' => $response->status(),
        ]);

        return $this->handleJsonResponse($response);
    }

    /**
     * Save/update a contract instance.
     */
    public function updateContract(array $contractInstance): bool
    {
        $payload = [
            'ContractInstance' => $contractInstance,
        ];

        ray([
            'step' => 'updateContract_before',
            'url' => $this->baseUrl . '/api/contracts/ContractInstances',
            'contract_id' => $contractInstance['ContractId'] ?? null,
            'payload_size' => strlen(json_encode($payload)),
        ]);

        $response = $this->request()
            ->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => '*/*',
            ])
            ->timeout(180)
            ->post('/api/contracts/ContractInstances', $payload);

        ray([
            'step' => 'updateContract_after',
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'HIA update contract failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return true;
    }

    /**
     * Create, fetch, update XML, and save in one go.
     */
    public function createAndPopulateContract(int $dataTemplateId, array $data = []): array
    {
        $contractId = $this->createContract($dataTemplateId);

        $contract = $this->getContractById($contractId);

        $contract = $this->applyContractData($contract, $data);

        // TEMP: stop here for debugging
        ray([
            'step' => 'before_update_return',
            'contract_id' => $contractId,
            'job_number' => $contract['JobNumber'] ?? null,
            'client' => $contract['Client'] ?? null,
            'source_length' => isset($contract['Source']) ? strlen($contract['Source']) : 0,
        ]);

        return $contract;

        // $this->updateContract($contract);
        // return $this->getContractById($contractId);
    }

    /**
     * Apply your app data to both top-level fields and XML Source.
     */
    public function applyContractData(array $contract, array $data): array
    {
        $sourceXml = $contract['Source'] ?? null;

        if (blank($sourceXml)) {
            throw new RuntimeException('Contract Source XML is empty.');
        }

        $xml = $this->loadXml($sourceXml);
        $namespaces = $xml->getNamespaces(true);

        // Register common namespaces for XPath usage
        if (isset($namespaces['hia'])) {
            $xml->registerXPathNamespace('hia', $namespaces['hia']);
        }
        if (isset($namespaces['ns1'])) {
            $xml->registerXPathNamespace('ns1', $namespaces['ns1']);
        }

        // Top-level JSON fields
        if (isset($data['job_number'])) {
            $contract['JobNumber'] = $data['job_number'];
            $this->setXmlValue($xml, '//hia:form/job_code', $data['job_number']);
        }

        if (isset($data['client'])) {
            $contract['Client'] = $data['client'];
        }

        if (isset($data['contract_date'])) {
            $this->setXmlValue($xml, '//contract_date', $data['contract_date']);
        }

        // Owner fields
        if (isset($data['owner'])) {
            $owner = $data['owner'];

            $this->setXmlValue($xml, '//owners/hia:owner/name/title', $owner['title'] ?? null);
            $this->setXmlValue($xml, '//owners/hia:owner/name/firstname', $owner['firstname'] ?? null);
            $this->setXmlValue($xml, '//owners/hia:owner/name/givennames', $owner['givennames'] ?? ($owner['firstname'] ?? null));
            $this->setXmlValue($xml, '//owners/hia:owner/name/lastname', $owner['lastname'] ?? null);
            $this->setXmlValue($xml, '//owners/hia:owner/name/fullname', $owner['fullname'] ?? null);

            $this->setXmlValue($xml, '//owners/hia:owner/address/line1', $owner['address_line1'] ?? null);
            $this->setXmlValue($xml, '//owners/hia:owner/address/suburb', $owner['suburb'] ?? null);
            $this->setXmlValue($xml, '//owners/hia:owner/address/state', $owner['state'] ?? null);
            $this->setXmlValue($xml, '//owners/hia:owner/address/postcode', $owner['postcode'] ?? null);
            $this->setXmlValue($xml, '//owners/hia:owner/address/country', $owner['country'] ?? 'Australia');

            $this->setXmlValue($xml, '//owners/hia:owner/contact/mobile', $owner['mobile'] ?? null);
            $this->setXmlValue($xml, '//owners/hia:owner/contact/email', $owner['email'] ?? null);

            if (!isset($data['client']) && !empty($owner['fullname'])) {
                $contract['Client'] = $owner['fullname'];
            }
        }

        // Builder fields
        if (isset($data['builder'])) {
            $builder = $data['builder'];

            $this->setXmlValue($xml, '//hia:builder/name/type', $builder['type'] ?? 'entity');
            $this->setXmlValue($xml, '//hia:builder/name/organisation', $builder['organisation'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/name/fullname', $builder['fullname'] ?? ($builder['organisation'] ?? null));
            $this->setXmlValue($xml, '//hia:builder/address/line1', $builder['address_line1'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/address/suburb', $builder['suburb'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/address/state', $builder['state'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/address/postcode', $builder['postcode'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/contact/workphone', $builder['workphone'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/contact/email', $builder['email'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/licensenumber', $builder['licence_number'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/abn', $builder['abn'] ?? null);
            $this->setXmlValue($xml, '//hia:builder/acn', $builder['acn'] ?? null);
        }

        // Site address
        if (isset($data['site'])) {
            $site = $data['site'];

            $this->setXmlValue($xml, '//hia:site/line1', $site['line1'] ?? null);
            $this->setXmlValue($xml, '//hia:site/suburb', $site['suburb'] ?? null);
            $this->setXmlValue($xml, '//hia:site/state', $site['state'] ?? null);
            $this->setXmlValue($xml, '//hia:site/postcode', $site['postcode'] ?? null);
            $this->setXmlValue($xml, '//hia:site/country', $site['country'] ?? 'Australia');
        }

        $contract['Source'] = $this->xmlToString($xml);

        return $contract;
    }

    protected function handleJsonResponse(Response $response): array
    {
        if (!$response->successful()) {
            throw new RuntimeException(
                'HIA API request failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response->json();
    }

    protected function loadXml(string $xmlString): SimpleXMLElement
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($xmlString);

        if ($xml === false) {
            $errors = collect(libxml_get_errors())
                ->map(fn($error) => trim($error->message))
                ->implode('; ');

            libxml_clear_errors();

            throw new RuntimeException('Invalid HIA XML: ' . $errors);
        }

        libxml_clear_errors();

        return $xml;
    }

    protected function xmlToString(SimpleXMLElement $xml): string
    {
        $xmlString = $xml->asXML();

        if ($xmlString === false) {
            throw new RuntimeException('Failed converting XML back to string.');
        }

        return $xmlString;
    }

    /**
     * Set a node value using XPath if the node exists.
     */
    protected function setXmlValue(SimpleXMLElement $xml, string $xpath, ?string $value): void
    {
        $nodes = $xml->xpath($xpath);

        if (!$nodes || !isset($nodes[0])) {
            return;
        }

        $nodes[0][0] = $value ?? '';
    }
}