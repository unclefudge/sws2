<?php

namespace App\Support;

use App\Models\Comms\Todo;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocPeriodTrade;
use App\Models\Company\CompanyDocReview;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\Incident\SiteIncidentWitness;
use App\Models\Site\SiteAccident;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteFoc;
use App\Models\Site\SiteFocItem;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceItem;
use App\Models\Site\SitePracCompletion;
use App\Models\Site\SitePracCompletionItem;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteScaffoldHandover;
use App\Models\Site\SiteShutdown;
use App\Models\User\UserDoc;
use App\User;

/**
 * Central registry for every SafeWorkSite Todo type.
 *
 * -----------------------------------------------------------------------------
 * WHY THIS EXISTS
 * -----------------------------------------------------------------------------
 * Historically a new Todo type had to be added in several separate places:
 * helpers.php (TODO_TYPES), Todo::record(), Todo::url(), parent model todos(),
 * TodoController and one or more Blade views. Missing one of those steps could
 * leave a Todo with the wrong type, broken link, or no parent record.
 *
 * This registry is now the single source of truth for the parts that can be
 * described as configuration: type name, label, parent record, destination URL,
 * assigned-task type, task name, action table and create permission.
 *
 * Wherever practical, use the constants in this class instead of typing Todo
 * type strings manually. That prevents mismatches such as "site_foc_task" vs
 * "foc_task".
 *
 * -----------------------------------------------------------------------------
 * ADDING A NEW ORDINARY TODO TYPE
 * -----------------------------------------------------------------------------
 * 1. Add a constant when the type will be referenced from application code:
 *
 *      public const CLIENT_APPROVAL = 'client_approval';
 *
 * 2. Add one definition in definitions():
 *
 *      self::CLIENT_APPROVAL => [
 *          'label' => 'Client Approval',
 *          'record' => ClientApproval::class,
 *          'url' => '/client/approval/{id}',
 *      ],
 *
 * 3. When creating the Todo, use the constant:
 *
 *      Todo::create([
 *          'type' => TodoTypeRegistry::CLIENT_APPROVAL,
 *          'type_id' => $approval->id,
 *          ...
 *      ]);
 *
 * Usually you should NOT also update helpers.php, Todo::record() or Todo::url().
 * Those now read from this registry.
 *
 * -----------------------------------------------------------------------------
 * ADDING AN "ASSIGNED TASKS" SECTION TO A MODULE
 * -----------------------------------------------------------------------------
 * A module such as FOC has two related Todo types:
 *
 *      foc         = Todo directly representing / linking to the FOC record
 *      foc_task    = manually assigned task belonging to that FOC record
 *
 * Define both types and configure the parent with:
 *
 *      'task_type'    => self::FOC_TASK,
 *      'task_name'    => fn(SiteFoc $record) => 'FOC Task @ ...',
 *      'action_table' => 'site_foc',
 *      'can_add_task' => fn(SiteFoc $record, User $user) => ...,
 *
 * Then the Blade page can use the reusable Livewire component:
 *
 *      <livewire:misc.assigned-tasks context="foc" :context-id="$foc->id"/>
 *
 * The component can then determine the Todo type, parent record, task name,
 * action table and permission from this registry instead of hard-coding them.
 *
 * -----------------------------------------------------------------------------
 * DEFINITION KEYS
 * -----------------------------------------------------------------------------
 * label
 *   Human-readable name used by TODO_TYPES / UI lists.
 *
 * record
 *   Either a model class, e.g. SiteFoc::class, or a closure for unusual links.
 *   A class resolves as Model::find($todo->type_id).
 *
 * url
 *   Either a URL template or closure. Supported placeholders are:
 *      {id}      = $todo->type_id
 *      {id2}     = $todo->type_id2
 *      {todo_id} = $todo->id
 *   If omitted, Todo::url() falls back to /todo/{todo_id}.
 *
 * task_type
 *   Todo type created by the reusable Assigned Tasks component for this parent.
 *
 * task_name
 *   Closure that generates the default name for an assigned task.
 *
 * action_table
 *   Action.table value used when recording task-related activity on the parent.
 *
 * can_add_task
 *   Closure deciding whether a user may add assigned tasks to the parent record.
 *
 * -----------------------------------------------------------------------------
 * CHANGING AN EXISTING TYPE STRING
 * -----------------------------------------------------------------------------
 * Changing the key/constant here does NOT update existing rows in the todo table.
 * If a stored type is renamed, add a database migration, for example:
 *
 *      DB::table('todo')
 *          ->where('type', 'site_foc_task')
 *          ->update(['type' => TodoTypeRegistry::FOC_TASK]);
 *
 * This should be rare once application code consistently uses registry constants.
 *
 * -----------------------------------------------------------------------------
 * SPECIAL BUSINESS LOGIC
 * -----------------------------------------------------------------------------
 * This registry should describe Todo configuration, not hide complex module
 * behaviour. If a module needs extra work when a task is created/completed
 * (special email recipients, touching another model, extra Actions, etc.), keep
 * that behaviour explicit in the relevant service/component/hook and document it.
 *
 * -----------------------------------------------------------------------------
 * QUICK CHECKLIST FOR A NEW TODO TYPE
 * -----------------------------------------------------------------------------
 * [ ] Add constant (recommended if application code refers to the type).
 * [ ] Add exactly one entry to definitions().
 * [ ] Set label.
 * [ ] Set record resolver when the Todo belongs to another record.
 * [ ] Set url when it should link somewhere other than /todo/{id}.
 * [ ] For Assigned Tasks: set task_type, task_name, action_table, can_add_task.
 * [ ] Create Todos using the registry constant, never a new handwritten variant.
 * [ ] If renaming an existing stored type, add a database migration.
 * [ ] Test: create -> email -> list -> open link -> complete/re-open.
 */
class TodoTypeRegistry
{
    public const HAZARD = 'hazard';
    public const ACCIDENT = 'accident';
    public const FOC = 'foc';
    public const FOC_TASK = 'foc_task';
    public const FOC_ITEM = 'foc_item';
    public const MAINTENANCE = 'maintenance';
    public const MAINTENANCE_TASK = 'maintenance_task';
    public const MAINTENANCE_ITEM = 'maintenance_item';
    public const PRAC_COMPLETION = 'prac_completion';
    public const PRAC_COMPLETION_TASK = 'prac_completion_task';
    public const PRAC_COMPLETION_ITEM = 'prac_completion_item';

    /**
     * Single source of truth for Todo types.
     *
     * Add a new Todo type here rather than separately updating helpers,
     * Todo::record(), Todo::url(), and parent task components.
     */
    public static function definitions(): array
    {
        return [
            'incident' => ['label' => 'Incident Report', 'record' => SiteIncident::class],
            'incident prevent' => ['label' => 'Incident Preventative Action', 'record' => SiteIncident::class],
            'incident witness' => [
                'label' => 'Incident Witness',
                'url' => function (Todo $todo): string {
                    $witness = SiteIncidentWitness::find($todo->type_id);
                    return $witness && $witness->incident ? "/site/incident/{$witness->incident->id}/witness/{$todo->type_id}" : "/todo/{$todo->id}";
                },
            ],
            'incident review' => ['label' => 'Incident Review', 'record' => SiteIncident::class, 'url' => '/site/incident/{id}'],
            self::ACCIDENT => [
                'label' => 'Accident Report',
                'record' => SiteAccident::class,
                'task_type' => self::ACCIDENT,
                'task_name' => fn(SiteAccident $record): string => 'Site Accident Task @ ' . ($record->site?->name ?? "Accident {$record->id}"),
                'action_table' => 'site_accidents',
                'can_add_task' => fn(SiteAccident $record, User $user): bool => (bool)$record->status &&
                    $user->allowed2('edit.site.accident', $record) &&
                    $user->isCompany($record->owned_by->id),
            ],
            self::HAZARD => [
                'label' => 'Site Hazard',
                'record' => SiteHazard::class,
                'task_type' => self::HAZARD,
                'task_name' => fn(SiteHazard $record): string => 'Site Hazard Task @ ' . ($record->site?->name ?? "Hazard {$record->id}"),
                'action_table' => 'site_hazards',
                'can_add_task' => fn(SiteHazard $record, User $user): bool => (bool)$record->status &&
                    $user->allowed2('edit.site.hazard', $record) &&
                    $user->isCompany($record->owned_by->id),
            ],
            'asbestos notify' => ['label' => 'Asbestos Notification', 'url' => '/site/asbestos/notification/{id}/edit'],
            'extension' => ['label' => 'Contract Time Extensions', 'record' => SiteExtension::class, 'url' => '/site/extension'],
            'extension signoff' => ['label' => 'Contract Time Extensions', 'record' => SiteExtension::class, 'url' => '/site/extension'],
            'super checklist' => ['label' => 'Supervisor Checklist', 'record' => SuperChecklist::class, 'url' => '/supervisor/checklist/{id}/{id2}'],
            'super checklist signoff' => ['label' => 'Supervisor Checklist', 'record' => SuperChecklist::class, 'url' => '/supervisor/checklist/{id}/weekly'],
            'equipment' => ['label' => 'Equipment Transfer', 'record' => EquipmentLocation::class],

            self::MAINTENANCE => [
                'label' => 'Site Maintenance Requests',
                'record' => SiteMaintenance::class,
                'url' => '/site/maintenance/{id}',
                'task_type' => self::MAINTENANCE_TASK,
                'task_name' => fn(SiteMaintenance $record): string => 'Site Maintenance Task @ ' . ($record->site?->name ?? "Maintenance {$record->id}"),
                'action_table' => 'site_maintenance',
                'can_add_task' => fn(SiteMaintenance $record, User $user): bool => (bool)$record->status && $user->hasAnyRole2('con-construction-manager|con-administrator|web-admin|mgt-general-manager'),
            ],
            self::MAINTENANCE_TASK => ['label' => 'Site Maintenance Task', 'record' => SiteMaintenance::class],
            self::MAINTENANCE_ITEM => [
                'label' => 'Site Maintenance Item',
                'record' => fn(int $id) => optional(SiteMaintenanceItem::find($id))->maintenance,
                'url' => function (Todo $todo): string {
                    $item = SiteMaintenanceItem::find($todo->type_id);
                    return $item && $item->maintenance ? "/site/maintenance/{$item->maintenance->id}" : "/todo/{$todo->id}";
                },
            ],

            self::PRAC_COMPLETION => [
                'label' => 'Prac Completion',
                'record' => SitePracCompletion::class,
                'url' => '/site/prac-completion/{id}',
                'task_type' => self::PRAC_COMPLETION_TASK,
                'task_name' => fn(SitePracCompletion $record): string => 'Prac Completion Task @ ' . ($record->site?->name ?? "Prac Completion {$record->id}"),
                'action_table' => 'site_prac_completion',
                'can_add_task' => fn(SitePracCompletion $record, User $user): bool => (bool)$record->status && $user->hasAnyRole2('con-construction-manager|con-administrator|web-admin|mgt-general-manager'),
            ],
            self::PRAC_COMPLETION_TASK => ['label' => 'Prac Completion Task', 'record' => SitePracCompletion::class],
            self::PRAC_COMPLETION_ITEM => [
                'label' => 'Prac Completion Item',
                'record' => fn(int $id) => optional(SitePracCompletionItem::find($id))->prac,
                'url' => function (Todo $todo): string {
                    $item = SitePracCompletionItem::find($todo->type_id);
                    return $item && $item->prac ? "/site/prac-completion/{$item->prac->id}" : "/todo/{$todo->id}";
                },
            ],

            self::FOC => [
                'label' => 'FOC Requirements',
                'record' => SiteFoc::class,
                'url' => '/site/foc/{id}',
                'task_type' => self::FOC_TASK,
                'task_name' => fn(SiteFoc $record): string => 'FOC Task @ ' . ($record->site?->name ?? "FOC {$record->id}"),
                'action_table' => 'site_foc',
                'can_add_task' => fn(SiteFoc $record, User $user): bool => (bool)$record->status && $user->hasAnyRole2('con-construction-manager|con-administrator|web-admin|mgt-general-manager'),
            ],
            self::FOC_TASK => ['label' => 'FOC Assigned Task', 'record' => SiteFoc::class],
            self::FOC_ITEM => [
                'label' => 'FOC Item',
                'record' => fn(int $id) => optional(SiteFocItem::find($id))->foc,
                'url' => function (Todo $todo): string {
                    $item = SiteFocItem::find($todo->type_id);
                    return $item && $item->foc ? "/site/foc/{$item->foc->id}" : "/todo/{$todo->id}";
                },
            ],

            'dial_before_dig' => ['label' => 'Dial Before You Dig', 'url' => '/site/doc'],
            'inspection' => ['label' => 'Site Inspection'],
            'supervisor' => ['label' => 'Supervisor Checkin'],
            'inspection_electrical' => ['label' => 'Electrical Inspection Reports', 'record' => SiteInspectionElectrical::class, 'url' => '/site/inspection/electrical/{id}'],
            'inspection_plumbing' => ['label' => 'Plumbing Inspection Reports', 'record' => SiteInspectionPlumbing::class, 'url' => '/site/inspection/plumbing/{id}'],
            'scaffold handover' => ['label' => 'Scaffold Handover Certificate', 'record' => SiteScaffoldHandover::class, 'url' => '/site/scaffold/handover/{id}/edit'],
            'site shutdown' => ['label' => 'Site Shutdown', 'record' => SiteShutdown::class, 'url' => '/site/shutdown/{id}/edit'],
            'project supply' => ['label' => 'Project Supply Information', 'record' => SiteProjectSupply::class, 'url' => '/site/supply/{id}/edit'],
            'toolbox' => ['label' => 'Toolbox Talks', 'record' => ToolboxTalk::class, 'url' => '/safety/doc/toolbox3/{id}'],
            'swms' => ['label' => 'Safe Work Method Statements', 'record' => WmsDoc::class],
            'qa' => ['label' => 'Quality Assurance Reports', 'record' => SiteQa::class, 'url' => '/site/qa/{id}'],
            'company doc' => [
                'label' => 'Company Document',
                'record' => CompanyDoc::class,
                'url' => function (Todo $todo): string {
                    $doc = CompanyDoc::find($todo->type_id);
                    if (!$doc) return "/todo/{$todo->id}";
                    return ($doc->expiry && $doc->expiry->gt(now())) ? "/company/{$doc->for_company_id}/doc/{$doc->id}/edit" : "/company/{$doc->for_company_id}/doc";
                },
            ],
            'company ptc' => [
                'label' => 'Period Trade Contract',
                'url' => function (Todo $todo): string {
                    $ptc = CompanyDocPeriodTrade::find($todo->type_id);
                    return $ptc ? "/company/{$ptc->for_company_id}/doc/period-trade-contract/{$todo->type_id}" : "/todo/{$todo->id}";
                },
            ],
            'company privacy' => ['label' => 'Company Privacy Policy'],
            'company doc review' => [
                'label' => 'Standard Details Review',
                'record' => CompanyDocReview::class,
                'url' => function (Todo $todo): string {
                    $doc = CompanyDocReview::find($todo->type_id);
                    return $doc ? "/company/doc/standard/review/{$doc->id}/edit" : "/todo/{$todo->id}";
                },
            ],
            'user doc' => ['label' => 'User Documents', 'record' => UserDoc::class],
        ];
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::definitions() as $type => $definition)
            $options[$type] = $definition['label'];

        return $options;
    }

    public static function definition(string $type): ?array
    {
        return self::definitions()[$type] ?? null;
    }

    public static function record(string $type, int $id)
    {
        $resolver = self::definition($type)['record'] ?? null;

        if (!$resolver)
            return null;

        if (is_string($resolver))
            return $resolver::find($id);

        return $resolver($id);
    }

    public static function url(Todo $todo): string
    {
        $url = self::definition($todo->type)['url'] ?? null;

        if (!$url)
            return "/todo/{$todo->id}";

        if (is_callable($url))
            return $url($todo);

        return str_replace(['{id}', '{id2}', '{todo_id}'], [$todo->type_id, $todo->type_id2, $todo->id], $url);
    }

    public static function taskType(string $context): string
    {
        return self::definition($context)['task_type'] ?? $context;
    }

    public static function taskName(string $context, $record): string
    {
        $name = self::definition($context)['task_name'] ?? null;
        return is_callable($name) ? $name($record) : (self::definition($context)['label'] ?? 'Task');
    }

    public static function actionTable(string $context): ?string
    {
        return self::definition($context)['action_table'] ?? null;
    }

    public static function canAddTask(string $context, $record, User $user): bool
    {
        $check = self::definition($context)['can_add_task'] ?? null;
        return $check ? (bool)$check($record, $user) : false;
    }
}
