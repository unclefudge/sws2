<?php

namespace App\Scheduled\Operations;

use App\Models\Comms\Todo;
use App\Models\Company\CompanyDoc;
use App\Models\Site\SiteQa;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use RuntimeException;
use Throwable;

class TodoReconcileOperation implements ScheduledOperationHandler
{
    private const STANDARD_RECORD_TYPES = [
        'extension', 'extension signoff', 'super checklist', 'super checklist signoff', 'equipment', 'maintenance', 'maintenance_item', 'supervisor',
        'inspection_electrical', 'inspection_plumbing', 'scaffold handover', 'project supply',
    ];

    public static function scheduledOperation(): array
    {
        return [
            // This replaces the old rogueToDo handler. The separate Company
            // Document and QA ToDo operations can be archived after deployment.
            'key' => 'nightly.rogue_todos',
            'name' => 'Reconcile QAs and active ToDos',
            'category' => 'maintenance',
            'description' => 'Reactivates completed on-hold QAs, then closes completed, replaced or orphaned Company Document, QA and standard ToDos.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'No email is sent by this operation',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        // This must run before QA ToDos are checked. In the old nightly controller,
        // a fully completed on-hold QA was returned to Active first, which kept its
        // ToDo open. Other on-hold QAs remained on hold and had their ToDos closed.
        // Keeping both steps in this handler prevents independent schedules from
        // running them in the opposite order and changing the result.
        $reactivatedQaCount = $this->reactivateCompletedOnHoldQas();
        $todos = Todo::query()->where('status', 1)->orderBy('id')->get();
        $companyDocs = CompanyDoc::query()->with('company')->whereIn('id', $todos->where('type', 'company doc')->pluck('type_id')->unique())->get()->keyBy('id');
        $qas = SiteQa::query()->whereIn('id', $todos->where('type', 'qa')->pluck('type_id')->unique())->get()->keyBy('id');
        $closedByReason = [];
        $errors = [];
        $firstException = null;

        echo "Active ToDos checked: {$todos->count()}.\n";

        foreach ($todos as $todo) {
            try {
                $reason = match ($todo->type) {
                    'company doc' => $this->companyDocumentReason($todo, $companyDocs),
                    'qa' => $this->qaReason($todo, $qas),
                    default => $this->standardOrOrphanReason($todo),
                };

                if (!$reason) continue;

                $this->closeTodo($todo);
                $closedByReason[$reason] = ($closedByReason[$reason] ?? 0) + 1;
                echo "Closed ToDo [{$todo->id}] {$todo->name}: {$reason}.\n";
            } catch (Throwable $exception) {
                $firstException ??= $exception;
                $errors[] = "ToDo [{$todo->id}] {$todo->name}: {$exception->getMessage()}";
                echo "Unable to reconcile ToDo [{$todo->id}] {$todo->name}: {$exception->getMessage()}.\n";
            }
        }

        $closedCount = array_sum($closedByReason);
        foreach ($closedByReason as $reason => $count) echo "{$reason}: {$count}.\n";
        echo "ToDos closed: {$closedCount}.\n";

        // Completed records remain closed if a later record fails. The retry
        // safely skips them and concentrates on the records that still need work.
        if ($errors) {
            echo "Reconciliation errors: " . count($errors) . ".\n";
            throw new RuntimeException('Unable to reconcile ' . count($errors) . ' active ToDo(s). See the operation output for record details.', 0, $firstException);
        }

        echo "QAs reactivated: {$reactivatedQaCount}.\n";

        return $reactivatedQaCount + $closedCount;
    }

    private function reactivateCompletedOnHoldQas(): int
    {
        $qas = SiteQa::query()->with('items')->where('master', 0)->where('status', 2)->where('company_id', 3)->orderBy('id')->get();
        $reactivatedCount = 0;

        foreach ($qas as $qa) {
            if ($qa->items->count() !== $qa->itemsCompleted()->count()) continue;

            $qa->status = 1;
            $qa->save();
            $reactivatedCount++;
            echo "Moved QA [{$qa->id}] {$qa->name} from On Hold to Active.\n";
        }

        return $reactivatedCount;
    }

    private function companyDocumentReason(Todo $todo, $companyDocs): ?string
    {
        $document = $companyDocs->get($todo->type_id);
        if (!$document) return 'Company Document was deleted';

        // An inactive document remains outstanding until another active
        // document in the same category has actually replaced it.
        if ((int)$document->status !== 0) return null;
        if (!$document->company) return 'Company linked to the inactive document was deleted';

        return $document->company->activeCompanyDoc($document->category_id)
            ? 'Company Document was replaced'
            : null;
    }

    private function qaReason(Todo $todo, $qas): ?string
    {
        $qa = $qas->get($todo->type_id);
        if (!$qa) return 'QA record was deleted';

        return match ((int)$qa->status) {
            0 => 'QA was completed',
            2 => 'QA was placed on hold',
            -1 => 'QA was marked not required',
            default => null,
        };
    }

    private function standardOrOrphanReason(Todo $todo): ?string
    {
        $record = $todo->record();
        if (!$record) return 'Linked record was deleted';

        return in_array($todo->type, self::STANDARD_RECORD_TYPES, true) && (int)$record->status === 0
            ? 'Linked record was completed'
            : null;
    }

    private function closeTodo(Todo $todo): void
    {
        $todo->status = 0;
        $todo->done_at = now();
        $todo->done_by = 1;
        $todo->save();
    }
}
