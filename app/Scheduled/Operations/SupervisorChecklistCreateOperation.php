<?php

namespace App\Scheduled\Operations;

use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Supervisor\SuperChecklistResponse;
use App\Models\Misc\Supervisor\SuperChecklistSettings;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SupervisorChecklistCreateOperation implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.super_checklists',
            'name' => 'Create Supervisor checklists',
            'category' => 'maintenance',
            'description' => 'Creates the weekly checklist and required responses for selected Supervisors, refreshes weekday ToDos and archives earlier checklist weeks.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Selected Supervisors through the assigned checklist ToDo workflow',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $monday = Carbon::now()->startOfWeek()->startOfDay();
        $settings = SuperChecklistSettings::query()->where('field', 'supers')->where('status', 1)->first();
        $supervisorIds = collect(explode(',', (string)$settings?->value))->map(fn($id) => trim($id))->filter(fn($id) => ctype_digit($id))
            ->map(fn($id) => (int)$id)->unique()->values();
        $supervisors = User::query()->whereIn('id', $supervisorIds)->orderBy('firstname')->get();
        $checklistsCreated = 0;
        $responsesCreated = 0;
        $todoActions = 0;

        echo "Selected Supervisor IDs: " . ($supervisorIds->isEmpty() ? 'none' : $supervisorIds->implode(', ')) . ".\n";

        foreach ($supervisors as $supervisor) {
            if ($supervisor->name === 'TO BE ALLOCATED') {
                echo "Skipped placeholder Supervisor [{$supervisor->id}] {$supervisor->name}.\n";
                continue;
            }

            [$checklist, $created, $newResponses] = DB::transaction(function () use ($supervisor, $monday) {
                $checklist = SuperChecklist::query()->where('super_id', $supervisor->id)->whereDate('date', $monday)->first();
                if ($checklist) return [$checklist, false, 0];

                $checklist = SuperChecklist::create(['super_id' => $supervisor->id, 'date' => $monday->toDateTimeString(), 'status' => 1]);
                $newResponses = 0;

                for ($day = 1; $day <= 5; $day++) {
                    foreach ($checklist->questions()->sortBy('id') as $question) {
                        if (!$question->isRequiredForSupervisor($supervisor, $day)) continue;

                        SuperChecklistResponse::create(['checklist_id' => $checklist->id, 'day' => $day, 'question_id' => $question->id, 'status' => 1, 'created_by' => 1]);
                        $newResponses++;
                    }
                }

                return [$checklist, true, $newResponses];
            });

            if ($created) {
                $checklistsCreated++;
                $responsesCreated += $newResponses;
                echo "Created checklist [{$checklist->id}] for {$supervisor->name} with {$newResponses} required responses.\n";
            } else {
                echo "Using existing checklist [{$checklist->id}] for {$supervisor->name}.\n";
            }

            // The legacy task refreshed each selected Supervisor's checklist
            // ToDo every weekday, while retaining the same weekly checklist.
            if (Carbon::today()->isWeekday()) {
                $checklist->closeToDo();
                $checklist->createSupervisorToDo($supervisor->id);
                $todoActions++;
                echo "Refreshed the checklist ToDo for {$supervisor->name}.\n";
            }
        }

        $oldChecklists = SuperChecklist::query()->where('status', 1)->whereDate('date', '<', $monday)->orderBy('date')->get();
        foreach ($oldChecklists as $checklist) {
            $checklist->status = 0;
            $checklist->save();
        }

        if ($oldChecklists->isNotEmpty()) echo "Archived {$oldChecklists->count()} earlier active checklist(s).\n";
        if ($supervisors->isEmpty()) echo "No valid selected Supervisors were found; check the active 'supers' checklist setting.\n";
        echo "Checklists created: {$checklistsCreated}; responses created: {$responsesCreated}; Supervisor ToDos refreshed: {$todoActions}; old checklists archived: {$oldChecklists->count()}.\n";

        return $checklistsCreated + $responsesCreated + $todoActions + $oldChecklists->count();
    }
}
