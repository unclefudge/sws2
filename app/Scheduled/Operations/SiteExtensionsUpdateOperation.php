<?php

namespace App\Scheduled\Operations;

use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteExtensionSite;
use App\Models\Site\SiteNote;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;

class SiteExtensionsUpdateOperation implements ScheduledOperationHandler
{
    private const COMPANY_ID = 3;
    private const JOB_START_TASK_ID = 11;

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.site_extensions',
            'name' => 'Update weekly site extensions',
            'category' => 'maintenance',
            'description' => 'Maintains the current Contract Time Extension register, updates eligible sites and completion dates, regenerates its PDF and archives older weeks.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'No email is sent by this operation',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $monday = Carbon::parse('monday this week')->startOfDay();
        $sites = Site::query()->where('company_id', self::COMPANY_ID)->where('status', 1)->whereNull('special')->orderBy('name')->get();
        $jobStarts = SitePlanner::query()->whereIn('site_id', $sites->pluck('id'))->where('task_id', self::JOB_START_TASK_ID)->orderBy('id')->get()
            ->groupBy('site_id')->map(fn($records) => $records->first());
        $eligibleSites = $sites->filter(function (Site $site) use ($jobStarts, $monday) {
            $jobStart = $jobStarts->get($site->id);
            return $jobStart?->from && Carbon::parse($jobStart->from)->lte($monday) && !$site->completion_signed;
        });
        $orderedSites = $eligibleSites->filter(fn(Site $site) => $site->forecast_completion)
            ->sortBy(fn(Site $site) => Carbon::parse($site->forecast_completion)->timestamp)
            ->concat($eligibleSites->reject(fn(Site $site) => $site->forecast_completion)->sortBy('name'));
        $extension = SiteExtension::query()->whereDate('date', $monday->toDateString())->first();
        $extensionCreated = 0;

        if (!$extension) {
            $extension = SiteExtension::create(['date' => $monday->toDateTimeString(), 'status' => 1]);
            $extensionCreated = 1;
            echo "Created extension week " . $monday->format('d/m/Y') . ".\n";
        } else {
            echo "Using existing extension week " . $monday->format('d/m/Y') . ".\n";
        }

        $previousMonday = Carbon::now()->subWeek()->startOfWeek();
        $previousSunday = Carbon::now()->subWeek()->endOfWeek();
        $variationNotes = SiteNote::query()->where('category_id', 16)->where('variation_days', '>', 0)->whereIn('site_id', $eligibleSites->pluck('id'))
            ->whereBetween('created_at', [$previousMonday, $previousSunday])->whereNull('parent')->orderBy('created_at')->get()->groupBy('site_id');
        $extensionSites = SiteExtensionSite::query()->where('extension_id', $extension->id)->get()->keyBy('site_id');
        $sitesAdded = 0;
        $completionDatesUpdated = 0;

        foreach ($orderedSites as $site) {
            $extensionSite = $extensionSites->get($site->id);

            if (!$extensionSite) {
                $extensionSite = SiteExtensionSite::create(['extension_id' => $extension->id, 'site_id' => $site->id, 'completion_date' => $site->forecast_completion]);
                $reminder = $this->variationReminder($variationNotes->get($site->id, collect()));
                if ($reminder) {
                    $extensionSite->notes = $reminder;
                    $extensionSite->save();
                }
                $extensionSites->put($site->id, $extensionSite);
                $sitesAdded++;
                echo "Added site [{$site->id}] {$site->name}.\n";
                continue;
            }

            if ($this->dateKey($extensionSite->completion_date) === $this->dateKey($site->forecast_completion)) continue;

            $extensionSite->completion_date = $site->forecast_completion;
            $extensionSite->save();
            $completionDatesUpdated++;
            echo "Updated completion date for site [{$site->id}] {$site->name}.\n";
        }

        $extension->refresh()->createPDF();
        echo "Regenerated the extension PDF.\n";

        $currentSites = SiteExtensionSite::query()->with('site.supervisor')->where('extension_id', $extension->id)->get();
        $supervisors = $currentSites->pluck('site.supervisor')->filter()->unique('id');
        $todosClosed = 0;

        foreach ($supervisors as $supervisor) {
            if ($extension->sitesNotCompletedBySupervisor($supervisor->id)->count()) continue;
            $todo = $supervisor->todoType('extension')->first();
            if (!$todo) continue;

            $todo->close();
            $todosClosed++;
            echo "Closed completed extension ToDo for {$supervisor->fullname}.\n";
        }

        $oldExtensions = SiteExtension::query()->where('status', 1)->whereDate('date', '<', $monday->toDateString())->orderBy('date')->get();
        foreach ($oldExtensions as $oldExtension) {
            $oldExtension->status = 0;
            $oldExtension->save();
            echo "Archived extension week " . $oldExtension->date->format('d/m/Y') . ".\n";
        }

        echo "Eligible sites: " . $eligibleSites->count() . ".\n";
        echo "Sites added: {$sitesAdded}; completion dates updated: {$completionDatesUpdated}; Supervisor ToDos closed: {$todosClosed}; old weeks archived: " . $oldExtensions->count() . ".\n";

        return $extensionCreated + $sitesAdded + $completionDatesUpdated + $todosClosed + $oldExtensions->count();
    }

    private function variationReminder($notes): string
    {
        return $notes->map(fn(SiteNote $note) => "Add [{$note->variation_days}] days for Approved Site Variation-[{$note->variation_name}] and include the SV number in the Notes as a reference")
            ->implode("\r\n");
    }

    private function dateKey($date): ?string
    {
        return $date ? Carbon::parse($date)->toDateString() : null;
    }
}
