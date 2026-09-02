<?php

namespace App\Livewire\Site\UpcomingJobs;

use App\Livewire\Concerns\NotifiesWithToastr;
use App\Models\Misc\Category;
use App\Models\Site\Site;
use App\Models\Site\SiteUpcomingSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Settings extends Component
{
    use NotifiesWithToastr {
        notify as notifyWithToastr;
    }

    private const STAGE_FIELDS = ['opt', 'cfest', 'cfadm'];

    public string $tab = 'stages';
    public array $stageRows = [];
    public array $steelRows = [];
    public array $siteOptions = [];
    public array $specialSiteIds = [];

    public bool $showAddStage = false;
    public string $newStageField = 'opt';
    public string $newStageName = '';
    public string $newStageText = '';
    public string $newStageColour = '';

    public bool $showAddSteel = false;
    public string $newSteelName = '';

    public bool $showRemove = false;
    public string $removeType = '';
    public string $removeAction = '';
    public ?int $removeId = null;
    public string $removeName = '';

    public ?string $warningMessage = null;

    public array $colours = [
        'col-blue-C5D1EC',
        'col-green-B5E2CD',
        'col-yellow-FFFAAE',
        'col-orange-FDD7B1',
        'col-red-FBB6B9',
        'col-purple-E4BFE4',
    ];

    public function mount(string $tab = 'stages'): void
    {
        $this->authoriseSettings();
        $this->tab = in_array($tab, ['stages', 'steel', 'sites'], true) ? $tab : 'stages';

        if ($this->tab === 'stages') {
            $this->loadStages();
        } elseif ($this->tab === 'steel') {
            $this->loadSteel();
        } else {
            $this->loadSites();
        }
    }

    public function openAddStage(string $field): void
    {
        $this->authoriseSettings();
        abort_unless(in_array($field, self::STAGE_FIELDS, true), 404);
        $this->resetValidation();
        $this->newStageField = $field;
        $this->newStageName = '';
        $this->newStageText = '';
        $this->newStageColour = '';
        $this->showAddStage = true;
    }

    public function addStage(): void
    {
        $this->authoriseSettings();
        abort_unless(in_array($this->newStageField, self::STAGE_FIELDS, true), 404);

        $this->validate([
            'newStageName' => ['required', 'string', 'max:255'],
            'newStageText' => ['nullable', 'string', 'max:255'],
            'newStageColour' => ['nullable', Rule::in($this->colours)],
        ], [], [
            'newStageName' => 'stage name',
            'newStageText' => 'default text',
            'newStageColour' => 'colour',
        ]);

        $legacyOrder = ((int)SiteUpcomingSettings::where('field', $this->newStageField)->max('order')) + 1;
        $sortOrder = ((int)SiteUpcomingSettings::where('field', $this->newStageField)->max('sort_order')) + 10;

        SiteUpcomingSettings::create([
            'field' => $this->newStageField,
            'name' => trim($this->newStageName),
            'value' => trim($this->newStageText),
            'colour' => $this->newStageColour ?: null,
            'order' => $legacyOrder,
            'sort_order' => $sortOrder,
            'status' => 1,
            'company_id' => Auth::user()->company_id,
        ]);

        $this->showAddStage = false;
        $this->loadStages();
        $this->notify('Stage option added.');
    }

    public function selectStageColour(string $field, string $rowKey, string $colour): void
    {
        abort_unless(in_array($colour, $this->colours, true), 404);
        $this->persistStageColour($field, $rowKey, $colour);
    }

    public function clearStageColour(string $field, string $rowKey): void
    {
        $this->persistStageColour($field, $rowKey, null);
    }

    public function reorderStage(string $field, array $orderedIds): void
    {
        $this->authoriseSettings();
        abort_unless(in_array($field, self::STAGE_FIELDS, true), 404);
        $this->stageRows[$field] = $this->reorderRows($this->stageRows[$field], $orderedIds);

        DB::transaction(function () use ($field): void {
            $this->assertStageRowsAreCurrent($field);

            foreach (array_values($this->stageRows[$field]) as $index => $row) {
                SiteUpcomingSettings::whereKey($row['id'])
                    ->where('field', $field)
                    ->where('status', 1)
                    ->update([
                        'sort_order' => ($index + 1) * 10,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }
        });

        $this->notify('Stage option order saved.');
        $this->skipRender();
    }

    public function saveStages(): void
    {
        $this->authoriseSettings();

        $rules = [];
        foreach (self::STAGE_FIELDS as $field) {
            $rules["stageRows.$field.*.name"] = ['required', 'string', 'max:255'];
            $rules["stageRows.$field.*.text"] = ['nullable', 'string', 'max:255'];
        }
        $this->validate($rules);

        DB::transaction(function (): void {
            foreach (self::STAGE_FIELDS as $field) {
                $this->assertStageRowsAreCurrent($field);

                foreach ($this->stageRows[$field] as $row) {
                    SiteUpcomingSettings::whereKey($row['id'])
                        ->where('field', $field)
                        ->where('status', 1)
                        ->update([
                            'name' => trim($row['name']),
                            'value' => trim($row['text'] ?? ''),
                            'updated_by' => Auth::id(),
                            'updated_at' => now(),
                        ]);
                }
            }
        });

        $this->loadStages();
        $this->notify('Stage options saved. Existing job stage values were not changed.');
    }

    public function openAddSteel(): void
    {
        $this->authoriseSettings();
        $this->resetValidation();
        $this->newSteelName = '';
        $this->showAddSteel = true;
    }

    public function addSteel(): void
    {
        $this->authoriseSettings();
        $this->validate(['newSteelName' => ['required', 'string', 'max:255']], [], ['newSteelName' => 'option name']);

        Category::create([
            'type' => 'upcoming_jobs_steel',
            'name' => trim($this->newSteelName),
            'order' => ((int)Category::where('type', 'upcoming_jobs_steel')->max('order')) + 1,
            'company_id' => Auth::user()->company->reportsTo()->id,
            'status' => 1,
        ]);

        $this->showAddSteel = false;
        $this->loadSteel();
        $this->notify('STEEL option added.');
    }

    public function reorderSteel(array $orderedIds): void
    {
        $this->authoriseSettings();
        $this->steelRows = $this->reorderRows($this->steelRows, $orderedIds);

        DB::transaction(function (): void {
            $this->assertSteelRowsAreCurrent();

            foreach (array_values($this->steelRows) as $index => $row) {
                Category::whereKey($row['id'])
                    ->where('type', 'upcoming_jobs_steel')
                    ->where('status', 1)
                    ->update([
                        'order' => $index + 1,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }
        });

        $this->notify('STEEL option order saved.');
        $this->skipRender();
    }

    public function saveSteel(): void
    {
        $this->authoriseSettings();
        $this->validate(['steelRows.*.name' => ['required', 'string', 'max:255']]);

        DB::transaction(function (): void {
            $this->assertSteelRowsAreCurrent();

            foreach ($this->steelRows as $row) {
                Category::whereKey($row['id'])
                    ->where('type', 'upcoming_jobs_steel')
                    ->where('status', 1)
                    ->update([
                        'name' => trim($row['name']),
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }
        });

        $this->loadSteel();
        $this->notify('STEEL options saved.');
    }

    public function requestRemove(string $type, int $id): void
    {
        $this->authoriseSettings();
        $this->warningMessage = null;

        if ($type === 'stage') {
            $setting = SiteUpcomingSettings::whereKey($id)->whereIn('field', self::STAGE_FIELDS)->where('status', 1)->firstOrFail();
            $this->removeAction = $this->stageUsageCount($setting) > 0 ? 'archive' : 'delete';
            $this->removeName = $setting->name;
        } elseif ($type === 'steel') {
            $category = Category::whereKey($id)->where('type', 'upcoming_jobs_steel')->where('status', 1)->firstOrFail();
            $this->removeAction = Site::where('steel', (string)$category->id)->exists() ? 'archive' : 'delete';
            $this->removeName = $category->name;
        } else {
            abort(404);
        }

        $this->removeType = $type;
        $this->removeId = $id;
        $this->showRemove = true;
    }

    public function removeOption(): void
    {
        $this->authoriseSettings();
        abort_unless($this->removeId && in_array($this->removeAction, ['delete', 'archive'], true), 404);

        if ($this->removeType === 'stage') {
            $setting = SiteUpcomingSettings::whereKey($this->removeId)->whereIn('field', self::STAGE_FIELDS)->where('status', 1)->firstOrFail();
            $inUse = $this->stageUsageCount($setting) > 0;

            if ($this->removeAction === 'delete') {
                if ($inUse) {
                    $this->closeModals();
                    $this->warningMessage = 'This option is now in use. Refresh the page and archive it instead.';
                    return;
                }
                $setting->delete();
            } else {
                $setting->update(['status' => 0]);
            }
            $this->loadStages();
        } elseif ($this->removeType === 'steel') {
            $category = Category::whereKey($this->removeId)->where('type', 'upcoming_jobs_steel')->where('status', 1)->firstOrFail();
            $inUse = Site::where('steel', (string)$category->id)->exists();

            if ($this->removeAction === 'delete') {
                if ($inUse) {
                    $this->closeModals();
                    $this->warningMessage = 'This option is now in use. Refresh the page and archive it instead.';
                    return;
                }
                $category->delete();
            } else {
                $category->update(['status' => 0]);
            }
            $this->loadSteel();
        } else {
            abort(404);
        }

        $notificationType = $this->removeAction === 'delete' ? 'error' : 'warning';
        $message = $this->removeAction === 'delete'
            ? 'Option deleted.'
            : 'Option archived. Its stored identifier was retained.';
        $this->closeModals();
        $this->notify($message, $notificationType);
    }

    public function saveSites(): void
    {
        $this->authoriseSettings();

        $allowedIds = array_map('strval', array_keys($this->siteOptions));
        $selected = collect($this->specialSiteIds)
            ->map(fn($id) => (string)$id)
            ->filter(fn($id) => in_array($id, $allowedIds, true))
            ->unique()
            ->values()
            ->all();

        abort_unless(count($selected) === count(array_unique(array_map('strval', $this->specialSiteIds))), 422, 'One or more selected sites are invalid.');

        $setting = SiteUpcomingSettings::firstOrNew(['field' => 'sites', 'status' => 1]);
        $setting->value = implode(',', $selected);
        $setting->company_id = $setting->company_id ?: Auth::user()->company_id;
        $setting->status = 1;
        $setting->save();

        $this->specialSiteIds = $selected;
        $this->notify('Additional sites saved.');
    }

    public function closeModals(): void
    {
        $this->showAddStage = false;
        $this->showAddSteel = false;
        $this->showRemove = false;
        $this->removeType = '';
        $this->removeAction = '';
        $this->removeId = null;
        $this->removeName = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.site.upcoming-jobs.settings');
    }

    private function loadStages(): void
    {
        foreach (self::STAGE_FIELDS as $field) {
            $this->stageRows[$field] = SiteUpcomingSettings::where('field', $field)
                ->where('status', 1)
                ->get()
                ->sortBy(fn($setting) => $setting->sort_order ?? (($setting->order ?? 0) * 10))
                ->mapWithKeys(fn($setting) => [
                    'row_' . $setting->id => [
                        'id' => $setting->id,
                        'legacy_value' => $setting->order,
                        'name' => $setting->name ?? '',
                        'text' => $setting->value ?? '',
                        'colour' => $setting->colour ?? '',
                        'in_use' => $this->stageUsageCount($setting) > 0,
                    ],
                ])->all();
        }
    }

    private function loadSteel(): void
    {
        $this->steelRows = Category::where('type', 'upcoming_jobs_steel')
            ->where('status', 1)
            ->orderBy('order')
            ->get()
            ->mapWithKeys(fn($category) => [
                'row_' . $category->id => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'in_use' => Site::where('steel', (string)$category->id)->exists(),
                ],
            ])->all();
    }

    private function loadSites(): void
    {
        $this->siteOptions = Auth::user()->company->sitesSelect();
        $setting = SiteUpcomingSettings::where('field', 'sites')->where('status', 1)->first();
        $this->specialSiteIds = $setting && $setting->value !== null && $setting->value !== ''
            ? array_values(array_filter(array_map('strval', explode(',', $setting->value))))
            : [];
    }

    private function stageUsageCount(SiteUpcomingSettings $setting): int
    {
        $value = $setting->order;

        if ($setting->field === 'opt') {
            return Site::where(function ($query) use ($value): void {
                $query->where('cc_stage', $value)
                    ->orWhere('fc_plans_stage', $value)
                    ->orWhere('fc_struct_stage', $value);
            })->count();
        }

        $column = $setting->field === 'cfest' ? 'cf_est_stage' : 'cf_adm_stage';
        return Site::where($column, $value)->count();
    }

    private function assertStageRowsAreCurrent(string $field): void
    {
        $incomingIds = collect($this->stageRows[$field])->pluck('id')->map(fn($id) => (int)$id)->sort()->values();
        $storedIds = SiteUpcomingSettings::where('field', $field)->where('status', 1)
            ->pluck('id')->map(fn($id) => (int)$id)->sort()->values();
        abort_unless($incomingIds->all() === $storedIds->all(), 409, 'The stage options changed. Refresh and try again.');
    }

    private function persistStageColour(string $field, string $rowKey, ?string $colour): void
    {
        $this->authoriseSettings();
        abort_unless(in_array($field, self::STAGE_FIELDS, true), 404);
        abort_unless(isset($this->stageRows[$field][$rowKey]), 404);

        $row = $this->stageRows[$field][$rowKey];
        $updated = SiteUpcomingSettings::whereKey($row['id'])
            ->where('field', $field)
            ->where('status', 1)
            ->update([
                'colour' => $colour,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

        abort_unless($updated === 1, 409, 'This stage option changed. Refresh and try again.');

        $this->stageRows[$field][$rowKey]['colour'] = $colour ?? '';
        $action = $colour === null ? 'cleared for' : 'updated for';
        $this->notify('Colour ' . $action . ' ' . $row['name'] . '.');
    }

    private function assertSteelRowsAreCurrent(): void
    {
        $incomingIds = collect($this->steelRows)->pluck('id')->map(fn($id) => (int)$id)->sort()->values();
        $storedIds = Category::where('type', 'upcoming_jobs_steel')->where('status', 1)
            ->pluck('id')->map(fn($id) => (int)$id)->sort()->values();
        abort_unless($incomingIds->all() === $storedIds->all(), 409, 'The options changed. Refresh and try again.');
    }

    private function reorderRows(array $rows, array $orderedIds): array
    {
        $orderedIds = array_map('intval', $orderedIds);
        $currentIds = array_map(fn(array $row) => (int)$row['id'], array_values($rows));
        $submittedIds = $orderedIds;
        sort($currentIds);
        sort($submittedIds);
        abort_unless($currentIds === $submittedIds, 422, 'The option order is invalid. Refresh and try again.');

        $ordered = [];
        foreach ($orderedIds as $id) {
            $key = 'row_' . $id;
            $ordered[$key] = $rows[$key];
        }

        return $ordered;
    }

    private function authoriseSettings(): void
    {
        abort_unless(Auth::check() && Auth::user()->hasPermission2('del.site.upcoming.compliance'), 403);
    }

    private function notify(string $message, string $type = 'success'): void
    {
        $this->warningMessage = null;
        $this->notifyWithToastr($message, $type);
    }
}
