<?php

namespace App\Jobs;

use App\Models\Misc\Category;
use App\Models\Site\SiteExtensionSite;
use App\Services\Zoho\ZohoCrmService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ZohoCreateTimeExtension implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $siteExtensionId)
    {
    }

    public function handle(ZohoCrmService $zoho): void
    {
        $siteExtension = SiteExtensionSite::with(['site', 'extension'])->findOrFail($this->siteExtensionId);

        if ($siteExtension->days <= 0 || !$siteExtension->extension->approved_by)
            return;

        if ($siteExtension->zoho_time_extension_id) {
            Log::info('Zoho time extension already exists', ['site_extension_id' => $siteExtension->id, 'zoho_time_extension_id' => $siteExtension->zoho_time_extension_id]);
            return;
        }

        if (!$siteExtension->site || !$siteExtension->site->zoho_job_id) {
            Log::warning('Zoho time extension skipped: missing Zoho Job ID', ['site_extension_id' => $siteExtension->id, 'site_id' => $siteExtension->site_id]);
            return;
        }

        if (!$siteExtension->site->zoho_contact_id) {
            throw new RuntimeException("Unable to create Zoho time extension: site {$siteExtension->site->code} is missing its Zoho Contact ID.");
        }

        try {
            $approvedAt = $siteExtension->extension->approved_at ?: now();
            $dateAdvised = Carbon::parse($approvedAt)->startOfWeek(Carbon::MONDAY)->addDays(4)->toDateString();
            $approvedBy = User::find($siteExtension->extension->approved_by);
            $reasons = collect($siteExtension->reasonsArray())->map(function ($categoryId) {
                return Category::find($categoryId)?->name;
            })->filter()->values()->all();

            $result = $zoho->createTimeExtension([
                'job_number' => $siteExtension->site->code,
                'job_name' => $siteExtension->site->zoho_job_id,
                'contact_lookup' => $siteExtension->site->zoho_contact_id,
                'date_advised' => $dateAdvised,
                'total_days_affected' => $siteExtension->days,
                'extend_reasons' => $reasons,
                'extend_notes' => $siteExtension->notes,
                'created_by' => $approvedBy?->full_name,
            ]);

            $zohoId = $result['zoho_time_extension_id'] ?? null;

            if (!$zohoId)
                throw new RuntimeException('Zoho time extension was created but no Zoho record ID was returned.');

            $siteExtension->zoho_time_extension_id = $zohoId;
            $siteExtension->save();
        } catch (Throwable $e) {
            Log::error('ZohoCreateTimeExtension failed', ['site_extension_id' => $siteExtension->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('ZohoCreateTimeExtension permanently failed', ['site_extension_id' => $this->siteExtensionId, 'error' => $e->getMessage()]);
    }
}
