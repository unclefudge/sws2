<?php

namespace App\Services\Hia;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

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
        $this->baseUrl = rtrim((string)config('services.hia.base_url'), '/');
        $this->username = (string)config('services.hia.username');
        $this->password = (string)config('services.hia.password');
        $this->clientId = (string)config('services.hia.client_id');
        $this->clientSecret = (string)config('services.hia.client_secret');
        $this->apiVersion = (string)config('services.hia.api_version', '1.0.0');
        $this->scope = (string)config('services.hia.scope', 'BASIC,COL,DIRECTAUTH');
    }

    public function token(): string
    {
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
            ])
                ->withBody($payload, 'application/x-www-form-urlencoded')
                ->connectTimeout(10)
                ->timeout(60)
                ->post($this->baseUrl . '/token');

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

    protected function request()
    {
        return Http::withToken($this->token())
            ->withHeaders([
                'Accept' => '*/*',
            ])
            ->baseUrl($this->baseUrl)
            ->connectTimeout(10)
            ->timeout(180);
    }

    public function getDataTemplates(): array
    {
        $response = $this->request()->get('/api/contracts/DataTemplates');

        return $this->handleJsonResponse($response);
    }

    public function getDataTemplateById(int $dataTemplateId): array
    {
        $response = $this->request()->get("/api/contracts/DataTemplates/{$dataTemplateId}");

        return $this->handleJsonResponse($response);
    }

    public function createContract(int $dataTemplateId): int
    {
        $response = $this->request()->put("/api/contracts/ContractInstances/{$dataTemplateId}");

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

    public function getContracts(): array
    {
        $response = $this->request()->get('/api/contracts/ContractInstances');

        if ($response->status() === 500 && str_contains($response->body(), 'No Contracts found')) {
            return [];
        }

        return $this->handleJsonResponse($response);
    }

    public function getContractById(int $contractId): array
    {
        $response = $this->request()->get("/api/contracts/ContractInstances/{$contractId}");

        return $this->handleJsonResponse($response);
    }

    public function updateContract(array $contractInstance): bool
    {
        $payload = [
            'ContractInstance' => $contractInstance,
        ];

        $response = $this->request()
            ->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => '*/*',
            ])
            ->timeout(180)
            ->post('/api/contracts/ContractInstances', $payload);

        if (!$response->successful()) {
            throw new RuntimeException(
                'HIA update contract failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return true;
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

    public function getContractPdf(int $contractId): string
    {
        $response = $this->request()
            ->withHeaders([
                'Accept' => '*/*',
            ])
            ->timeout(180)
            ->get("/api/contracts/PDF/{$contractId}");

        if (!$response->successful()) {
            throw new RuntimeException(
                'HIA get PDF failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        $pdf = base64_decode(trim($response->body(), '"'), true);

        if ($pdf === false) {
            throw new RuntimeException('HIA PDF response was not valid base64.');
        }

        return $pdf;
    }
}