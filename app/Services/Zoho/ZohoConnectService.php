<?php

namespace App\Services\Zoho;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ZohoConnectService
{
    protected string $tokenCacheKey = 'zoho_connect_access_token';

    protected function getAccessToken(string $type = 'connect'): string
    {
        $cacheKey = "zoho_{$type}_access_token";

        $refreshToken = match ($type) {
            'crm' => config('services.zoho.crm_refresh_token'),
            'connect' => config('services.zoho.connect_refresh_token'),
            default => throw new \InvalidArgumentException("Invalid Zoho token type: {$type}"),
        };

        return Cache::remember($cacheKey, now()->addMinutes(45), function () use ($refreshToken) {
            $response = Http::asForm()->post(config('services.zoho.accounts_url') . '/oauth/v2/token', [
                'refresh_token' => $refreshToken,
                'client_id' => config('services.zoho.client_id'),
                'client_secret' => config('services.zoho.client_secret'),
                'grant_type' => 'refresh_token',
            ]);

            if ($response->failed() || !$response->json('access_token')) {
                throw new \RuntimeException('Unable to get Zoho access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    public function findTaskByTitle(string|int $boardId, string $cardTitle): array
    {
        $accessToken = $this->getAccessToken('crm');

        $url = config('services.zoho.connect_url')
            . '/pulse/nativeapi/sectionTasks?scopeID=' . config('services.zoho.connect_scope_id')
            . '&boardId=' . $boardId . '&limit=100';

        $response = Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $accessToken, 'Accept' => 'application/json', 'User-Agent' => 'Mozilla/5.0',])->get($url);
        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey);

            return $this->findTaskByTitle($boardId, $cardTitle);
        }

        //dd(['status' => $response->status(), 'body' => $response->body(), 'json' => $response->json(), 'headers' => $response->headers()]);
        if ($response->failed()) {
            Log::error('Zoho sectionTasks failed', ['http_status' => $response->status(), 'body' => $response->body(), 'json' => $response->json(), 'headers' => $response->headers(),]);

            throw new \RuntimeException('Zoho sectionTasks failed. HTTP Status: ' . $response->status() . ' Body: ' . $response->body());
        }

        $tasks = data_get($response->json(), 'sectionTasks.tasks', []);

        $result = [
            'taskID' => null,
            'sectionID' => null,
            'sectionName' => null,
            'statusID' => null,
            'statusName' => null,
            'status' => null,
            'desc' => null,
            'customFields' => [],
            'found' => false,
        ];

        foreach ($tasks as $rec) {
            $title = data_get($rec, 'title.0.text');

            if (strtolower(trim($title)) === strtolower(trim($cardTitle))) {

                $result['found'] = true;
                $result['taskID'] = $rec['id'] ?? null;
                $result['sectionID'] = data_get($rec, 'section.id');
                $result['sectionName'] = data_get($rec, 'section.name');
                $result['desc'] = data_get($rec, 'desc.0.text');

                if (!empty($rec['taskStatus'])) {
                    $result['statusID'] = data_get($rec, 'taskStatus.id');
                    $result['statusName'] = data_get($rec, 'taskStatus.name');
                } else {
                    $result['status'] = $rec['status'] ?? null;
                }

                foreach (($rec['customRecords'] ?? []) as $key => $value) {
                    $result['customFields'][$key] = $value['value'] ?? null;
                }

                break;
            }
        }

        return $result;
    }

    public function updateTaskStatusByTitle($boardId, $cardTitle, $statusId)
    {
        $task = $this->findTaskByTitle($boardId, $cardTitle);
        if (!$task['found']) {
            throw new \RuntimeException("Task not found: {$cardTitle}");
        }

        return $this->updateTaskStatus($task['taskID'], $statusId);
    }

    public function updateTaskStatus(string|int $taskId, string|int $newStatusId): array
    {
        $response = $this->sendUpdateTaskRequest($taskId, $newStatusId);
        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey);
            $response = $this->sendUpdateTaskRequest($taskId, $newStatusId);
        }

        //Log::debug('Zoho Connect updateTask response', ['task_id' => $taskId, 'status_id' => $newStatusId, 'http_status' => $response->status(), 'body' => $response->body(), 'json' => $response->json(),]);
        if ($response->failed()) {
            throw new RuntimeException('Zoho Connect updateTask failed: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    protected function sendUpdateTaskRequest(string|int $taskId, string|int $newStatusId): Response
    {
        $accessToken = $this->getAccessToken('connect');

        return Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $accessToken, 'Content-Type' => 'application/json',
        ])->withQueryParameters([
            'scopeID' => config('services.zoho.connect_scope_id'),
            'taskId' => $taskId,
            'isCustom' => 'true',
            'status' => $newStatusId,
        ])->post(config('services.zoho.connect_url') . '/pulse/api/updateTask', []);
    }

    public function forgetAccessToken(): void
    {
        Cache::forget($this->tokenCacheKey);
    }
}