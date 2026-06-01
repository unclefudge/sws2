<?php

namespace App\Services\Zoho;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ZohoCrmService
{
    protected function getAccessToken(): string
    {
        return Cache::remember('zoho_crm_access_token', now()->addMinutes(45), function () {
            $response = Http::asForm()->post(config('services.zoho.accounts_url') . '/oauth/v2/token', [
                'refresh_token' => config('services.zoho.crm_refresh_token'),
                'client_id' => config('services.zoho.client_id'),
                'client_secret' => config('services.zoho.client_secret'),
                'grant_type' => 'refresh_token',
            ]);

            ray([
                'accounts_url' => config('services.zoho.accounts_url'),
                'crm_url' => config('services.zoho.crm_url'),
                'token_status' => $response->status(),
                'token_response' => collect($response->json())
                    ->except(['access_token'])
                    ->toArray(),
            ]);

            if ($response->failed() || !$response->json('access_token')) {
                throw new RuntimeException('Unable to get Zoho CRM access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    public function createVariation(array $data): array
    {
        $today = Carbon::now()->format('Y-m-d');
        $payload = [
            'data' => [
                [
                    'Var_Type' => $data['var_type'] ?? null,
                    'Job_Number' => $data['job_number'] ?? null,
                    'Job_Name' => $data['job_name'] ?? null,
                    'Product_Name' => $data['product_name'],
                    'Status' => $data['status'] ?? null,
                    'Super' => $data['super'] ?? null,
                    'Description' => $data['description'] ?? null,
                    // Cost fields
                    'Variation_Cost' => $data['variation_cost'] ?? null,
                    'Client_Price' => $data['client_price'] ?? null,
                    'Margin' => $data['margin'] ?? null,
                    'Debit_or_Credit' => $data['debit_or_credit'] ?? null,
                    // Date fields
                    'Date_1' => $today,
                    'Date_2' => $today,
                    'RFV_8_Released' => $today,
                ],
            ],
            //'trigger' => ['workflow',],
        ];

        $response = $this->sendCreateRecordRequest('Products', $payload);

        if ($response->status() === 401) {
            Cache::forget('zoho_crm_access_token');
            $response = $this->sendCreateRecordRequest('Products', $payload);
        }

        if ($response->failed()) {
            Log::error('Zoho CRM create variation failed', ['http_status' => $response->status(), 'body' => $response->body(), 'json' => $response->json(), 'payload' => $payload,]);

            throw new RuntimeException('Zoho CRM create variation failed. HTTP Status: ' . $response->status() . ' Body: ' . $response->body());
        }

        $json = $response->json();

        $result = data_get($json, 'data.0');

        if (($result['status'] ?? null) !== 'success') {
            Log::error('Zoho CRM create variation returned non-success', ['json' => $json, 'payload' => $payload,]);

            throw new RuntimeException('Zoho CRM create variation did not return success: ' . json_encode($json));
        }

        return [
            'success' => true,
            'zoho_product_id' => data_get($json, 'data.0.details.id'),
            'message' => data_get($json, 'data.0.message'),
            'raw' => $json,
        ];
    }

    protected function sendCreateRecordRequest(string $moduleApiName, array $payload): Response
    {
        $accessToken = $this->getAccessToken();

        ray([
            'crm_url' => config('services.zoho.crm_url'),
            'full_url' => config('services.zoho.crm_url') . '/' . $moduleApiName,
        ]);

        return Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $accessToken, 'Content-Type' => 'application/json', 'Accept' => 'application/json',])
            ->post(config('services.zoho.crm_url') . '/' . $moduleApiName, $payload);
    }
}