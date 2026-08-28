<?php

namespace App\Scheduled\Operations;

use App\Models\Comms\Todo;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteNote;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\User;
use Carbon\Carbon;
use RuntimeException;

class CreateSiteExtensionSupervisorTasksOperation implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.extension_task',
            'name' => 'Create site extension Supervisor tasks',
            'category' => 'maintenance',
            'description' => 'Creates each affected Site Supervisor\'s weekly Contract Time Extension ToDo and emails it through the assigned-ToDo workflow.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [2], 'time' => '00:05'], // Tuesday
            'recipients' => 'Affected Site Supervisors through their assigned ToDos',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $extension = SiteExtension::query()->with('sites.site')->where('status', 1)->latest('date')->first();
        if (!$extension) throw new RuntimeException('No active Contract Time Extension week exists. Run the site-extension update operation first.');

        $groups = $extension->sites->filter(fn($extensionSite) => !$extensionSite->reasons && $extensionSite->site?->supervisor_id)
            ->groupBy(fn($extensionSite) => (int) $extensionSite->site->supervisor_id);
        $siteIds = $groups->flatten(1)->pluck('site_id')->unique();
        $previousMonday = Carbon::now()->subWeek()->startOfWeek();
        $previousSunday = Carbon::now()->subWeek()->endOfWeek();
        $variationNotes = SiteNote::query()->with('site')->where('category_id', 16)->where('variation_days', '>', 0)->whereIn('site_id', $siteIds)
            ->whereBetween('created_at', [$previousMonday, $previousSunday])->whereNull('parent')->orderBy('created_at')->get()->groupBy('site_id');
        $createdCount = 0;

        foreach ($groups as $supervisorId => $extensionSites) {
            $supervisor = User::find((int) $supervisorId);
            if (!$supervisor) {
                echo "Skipped Supervisor [{$supervisorId}]: user no longer exists.\n";
                continue;
            }

            $sites = $extensionSites->pluck('site')->filter()->sortBy('name');
            $siteList = $sites->map(fn($site) => "- {$site->name}")->implode("\r\n");
            $notes = $variationNotes->only($sites->pluck('id')->all())->flatten(1);
            $noteReminder = $this->variationReminder($notes);
            $info = "Please complete the Contract Time Extensions for the following sites:\r\n{$siteList}{$noteReminder}";
            $existingTodo = $supervisor->todoType('extension', 1)->first();
            if ($existingTodo) $existingTodo->close();

            $todo = Todo::create([
                'type' => 'extension', 'type_id' => $extension->id, 'name' => 'Contract Time Extensions', 'info' => $info,
                'priority' => 1, 'due_at' => Carbon::tomorrow()->format('Y-m-d') . ' 14:00:00', 'company_id' => 3, 'created_by' => 1, 'updated_by' => 1,
            ]);
            $todo->assignUsers($supervisor->id);
            $todo->emailToDo();
            $createdCount++;
            echo "Created extension ToDo [{$todo->id}] for {$supervisor->fullname}: " . $sites->pluck('id')->implode(', ') . ".\n";
        }

        if (!$createdCount) echo "No Supervisor extension ToDos were required.\n";
        echo "Supervisor extension ToDos created: {$createdCount}.\n";

        return $createdCount;
    }

    private function variationReminder($notes): string
    {
        if ($notes->isEmpty()) return '';

        $lines = $notes->map(function (SiteNote $note) {
            $siteName = $note->site?->name ?: "Site #{$note->site_id}";
            return "- {$siteName}  Days: {$note->variation_days} Variation:{$note->variation_name}";
        })->implode("\r\n");
        return "\r\n\r\nPlease remember to add the following Site Note Variations from the previous week and include the SV number in the Notes as a reference:\r\n\r\n{$lines}";
    }
}
