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
    protected string $tokenCacheKey = 'zoho_crm_access_token';

    protected function getAccessToken(): string
    {
        return Cache::remember($this->tokenCacheKey, now()->addMinutes(45), function () {
            $response = Http::asForm()->post(config('services.zoho.accounts_url') . '/oauth/v2/token', [
                'refresh_token' => config('services.zoho.crm_refresh_token'),
                'client_id' => config('services.zoho.client_id'),
                'client_secret' => config('services.zoho.client_secret'),
                'grant_type' => 'refresh_token',
            ]);

            //ray(['accounts_url' => config('services.zoho.accounts_url'),'crm_url' => config('services.zoho.crm_url'),'token_status' => $response->status(),'token_response' => collect($response->json())->except(['access_token'])->toArray(),]);

            if ($response->failed() || !$response->json('access_token')) {
                throw new RuntimeException('Unable to get Zoho CRM access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    public function createVariation(array $data): array
    {
        $today = Carbon::now()->format('Y-m-d');
        $record = [
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
            'RFV_8_Released' => $data['RFV8_released'] ?? null,
        ];

        /*
         |--------------------------------------------------------------------------
         | Zoho file upload field
         |--------------------------------------------------------------------------
         | Not currently being used as we don't have any File Fields (but here for future possiblity)
         */
        /*if (!empty($data['file_ids'])) {
            $record['Name_Of_Module_Field'] = collect($data['file_ids'])->filter()->map(fn($fileId) => ['File_Id__s' => $fileId])->values()->all();
            ray($data['file_ids']);
        }*/

        // Remove null values so Zoho does not reject optional empty fields.
        $record = collect($record)->reject(fn($value) => $value === null)->toArray();

        $payload = [
            'data' => [$record,],
            'trigger' => ['workflow',],
        ];

        //Log::info('Zoho variation payload', ['file_ids' => $data['file_ids'] ?? [], 'record_file_field' => $record['File_Upload'] ?? null, 'payload' => $payload,]);

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
            'zoho_variation_id' => data_get($json, 'data.0.details.id'),
            'message' => data_get($json, 'data.0.message'),
            'raw' => $json,
        ];
    }

    public function createLead(array $leadData): array
    {
        $record = [
            'Enquiry_Date' => Carbon::now()->format('Y-m-d'),
            'Query_Taker' => 'WEBS',
        ];

        $extraFields = collect($leadData)->except(array_keys($record))->toArray();
        $record = array_merge($record, $extraFields);
        $record = collect($record)->reject(fn($value) => $value === null)->toArray();

        $payload = [
            'data' => [$record],
            //'trigger' => ['workflow'],
        ];

        //ray('Payload', $payload);
        $response = $this->sendCreateRecordRequest('Leads', $payload);

        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey);
            $response = $this->sendCreateRecordRequest('Leads', $payload);
        }

        if ($response->failed()) {
            Log::error('Zoho CRM create lead failed', ['http_status' => $response->status(), 'body' => $response->body(), 'json' => $response->json(), 'payload' => $payload,]);

            throw new RuntimeException('Zoho CRM create lead failed. HTTP Status: ' . $response->status() . ' Body: ' . $response->body());
        }

        $json = $response->json();
        $result = data_get($json, 'data.0');

        if (($result['status'] ?? null) !== 'success') {
            Log::error('Zoho CRM create lead returned non-success', ['json' => $json, 'payload' => $payload,]);

            throw new RuntimeException('Zoho CRM create lead did not return success: ' . json_encode($json));
        }

        return [
            'success' => true,
            'zoho_lead_id' => data_get($json, 'data.0.details.id'),
            'message' => data_get($json, 'data.0.message'),
            'raw' => $json,
        ];
    }

    public function createLeadOld(array $leadData): string
    {
        $response = Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $this->accessToken(), 'Accept' => 'application/json',])->post($this->apiDomain() . '/crm/v8/Leads', [
            'data' => [$leadData,],
            'trigger' => [
                'workflow',
            ],
        ]);

        $json = $response->json();

        if (!$response->successful()) {
            throw new RuntimeException('Zoho API HTTP error: ' . $response->body());
        }

        $status = data_get($json, 'data.0.status');
        $leadId = data_get($json, 'data.0.details.id');

        if ($status !== 'success' || !$leadId) {
            throw new RuntimeException('Zoho API create lead failed: ' . json_encode($json));
        }

        return $leadId;
    }

    protected function sendCreateRecordRequest(string $moduleApiName, array $payload): Response
    {
        $accessToken = $this->getAccessToken();

        return Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $accessToken, 'Content-Type' => 'application/json', 'Accept' => 'application/json',])
            ->post(config('services.zoho.crm_url') . '/' . $moduleApiName, $payload);
    }

    public function uploadAttachmentsToRecordFromStreams(string $moduleApiName, string $recordId, array $files): array
    {
        $files = collect($files)->filter(fn($file) => !empty($file['name']) && array_key_exists('contents', $file))->values();
        if ($files->isEmpty()) {
            return [];
        }

        return $files
            ->map(function ($file) use ($moduleApiName, $recordId) {
                $response = $this->sendUploadAttachmentToRecordRequest($moduleApiName, $recordId, $file);

                if ($response->status() === 401) {
                    Cache::forget($this->tokenCacheKey);
                    $response = $this->sendUploadAttachmentToRecordRequest($moduleApiName, $recordId, $file);
                }

                if ($response->failed()) {
                    Log::error('Zoho CRM attachment upload failed', ['module' => $moduleApiName, 'record_id' => $recordId, 'file_name' => $file['name'], 'http_status' => $response->status(), 'body' => $response->body(), 'json' => $response->json(),]);

                    throw new RuntimeException('Zoho CRM attachment upload failed. HTTP Status: ' . $response->status() . ' Body: ' . $response->body());
                }

                return [
                    'file_name' => $file['name'],
                    'zoho_attachment_id' => data_get($response->json(), 'data.0.details.id'),
                    'message' => data_get($response->json(), 'data.0.message'),
                    'raw' => $response->json(),
                ];
            })->values()->all();
    }

    protected function sendUploadAttachmentToRecordRequest(string $moduleApiName, string $recordId, array $file): Response
    {
        $accessToken = $this->getAccessToken();

        return Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $accessToken, 'Accept' => 'application/json',])
            ->attach('file', $file['contents'], $file['name'])
            ->post(rtrim(config('services.zoho.crm_url'), '/') . '/' . $moduleApiName . '/' . $recordId . '/Attachments');
    }

    // Used specifically for File fields within a Module (not linked Attachment list)
    // ** we don't current have any in Zoho but left this function for possible future needs
    public function uploadFilesToZfsFromStreams(array $files): array
    {
        $files = collect($files)->filter(fn($file) => !empty($file['name']) && array_key_exists('contents', $file))->values();

        if ($files->isEmpty()) {
            return [];
        }

        return $files
            ->chunk(10)
            ->flatMap(function ($chunk) {
                $response = $this->sendUploadStreamsToZfsRequest($chunk->all());

                if ($response->status() === 401) {
                    Cache::forget($this->tokenCacheKey);
                    Cache::forget('zoho_crm_api_domain');

                    $response = $this->sendUploadStreamsToZfsRequest($chunk->all());
                }

                if ($response->failed()) {
                    Log::error('Zoho CRM ZFS upload failed', ['http_status' => $response->status(), 'body' => $response->body(), 'json' => $response->json(),]);

                    throw new RuntimeException('Zoho CRM ZFS upload failed. HTTP Status: ' . $response->status() . ' Body: ' . $response->body());
                }

                return collect($response->json('data', []))
                    ->filter(fn($row) => ($row['status'] ?? null) === 'success')
                    ->map(fn($row) => data_get($row, 'details.id'))
                    ->filter();
            })->values()->all();
    }

    protected function sendUploadStreamsToZfsRequest(array $files): Response
    {
        $accessToken = $this->getAccessToken();

        $request = Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $accessToken, 'Accept' => 'application/json',]);

        foreach ($files as $file) {
            $request = $request->attach('file', $file['contents'], $file['name']);
        }

        return $request->post(config('services.zoho.crm_url') . '/files');
    }
}