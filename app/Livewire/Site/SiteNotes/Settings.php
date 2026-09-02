<?php

namespace App\Livewire\Site\SiteNotes;

use App\Livewire\Concerns\NotifiesWithToastr;
use App\Models\Misc\Category;
use App\Models\Site\SiteNote;
use App\Models\Site\SiteNoteCost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Settings extends Component
{
    use NotifiesWithToastr {
        notify as notifyWithToastr;
    }

    private const TYPES = [
        'category' => 'site_note',
        'cost-centre' => 'site_note_cost',
    ];

    public string $tab = 'categories';
    public array $categoryRows = [];
    public array $costCentreRows = [];
    public array $staffOptions = [];

    public bool $showAddOption = false;
    public string $newOptionType = 'category';
    public string $newOptionName = '';
    public array $newNotifyUsers = [];

    public bool $showRemove = false;
    public string $removeType = '';
    public string $removeAction = '';
    public ?int $removeId = null;
    public string $removeName = '';

    public function mount(string $tab = 'categories'): void
    {
        $this->authoriseSettings();
        $this->tab = in_array($tab, ['categories', 'cost-centres'], true) ? $tab : 'categories';
        $this->staffOptions = Auth::user()->company->staffSelect();

        $this->loadCurrentTab();
    }

    public function openAddOption(string $type): void
    {
        $this->authoriseSettings();
        $this->categoryType($type);
        $this->resetValidation();
        $this->newOptionType = $type;
        $this->newOptionName = '';
        $this->newNotifyUsers = [];
        $this->showAddOption = true;
    }

    public function addOption(): void
    {
        $this->authoriseSettings();
        $categoryType = $this->categoryType($this->newOptionType);
        $this->newOptionName = trim($this->newOptionName);

        $rules = ['newOptionName' => ['required', 'string', 'max:255']];
        if ($this->newOptionType === 'category') {
            $rules['newNotifyUsers'] = ['array'];
            $rules['newNotifyUsers.*'] = [Rule::in($this->staffIds())];
        }

        $this->validate($rules, [], [
            'newOptionName' => 'name',
            'newNotifyUsers' => 'users to notify',
        ]);

        Category::create([
            'type' => $categoryType,
            'name' => $this->newOptionName,
            'order' => ((int)$this->activeCategories($this->newOptionType)->max('order')) + 1,
            'notify_users' => $this->newOptionType === 'category'
                ? $this->serialiseUserIds($this->newNotifyUsers)
                : null,
            'company_id' => Auth::user()->company->reportsTo()->id,
            'status' => 1,
        ]);

        $type = $this->newOptionType;
        $this->closeModals();
        $this->loadRows($type);
        $this->notify($type === 'category' ? 'Category added.' : 'Cost centre added.');
    }

    public function reorderOptions(string $type, array $orderedIds): void
    {
        $this->authoriseSettings();
        $this->categoryType($type);

        $rows = $type === 'category' ? $this->categoryRows : $this->costCentreRows;
        $rows = $this->reorderRows($rows, $orderedIds);

        if ($type === 'category') {
            $this->categoryRows = $rows;
        } else {
            $this->costCentreRows = $rows;
        }

        DB::transaction(function () use ($type, $rows): void {
            $this->assertRowsAreCurrent($type, $rows);

            foreach (array_values($rows) as $index => $row) {
                Category::whereKey($row['id'])
                    ->where('type', $this->categoryType($type))
                    ->where('status', 1)
                    ->update([
                        'order' => $index + 1,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }
        });

        $this->notify($type === 'category' ? 'Category order saved.' : 'Cost centre order saved.');
        $this->skipRender();
    }

    public function saveCategories(): void
    {
        $this->authoriseSettings();

        foreach ($this->categoryRows as &$row) {
            $row['name'] = trim($row['name']);
        }
        unset($row);

        $this->validate([
            'categoryRows.*.name' => ['required', 'string', 'max:255'],
            'categoryRows.*.notify_users' => ['array'],
            'categoryRows.*.notify_users.*' => [Rule::in($this->staffIds())],
        ], [], [
            'categoryRows.*.name' => 'name',
            'categoryRows.*.notify_users' => 'users to notify',
        ]);

        DB::transaction(function (): void {
            $this->assertRowsAreCurrent('category', $this->categoryRows);

            foreach ($this->categoryRows as $row) {
                $category = Category::whereKey($row['id'])
                    ->where('type', self::TYPES['category'])
                    ->where('status', 1)
                    ->firstOrFail();
                $category->name = $row['name'];
                $category->notify_users = $this->serialiseUserIds($row['notify_users']);
                $category->save();
            }
        });

        $this->loadRows('category');
        $this->notify('Categories saved.');
    }

    public function saveCostCentres(): void
    {
        $this->authoriseSettings();

        foreach ($this->costCentreRows as &$row) {
            $row['name'] = trim($row['name']);
        }
        unset($row);

        $this->validate([
            'costCentreRows.*.name' => ['required', 'string', 'max:255'],
        ], [], [
            'costCentreRows.*.name' => 'name',
        ]);

        DB::transaction(function (): void {
            $this->assertRowsAreCurrent('cost-centre', $this->costCentreRows);

            foreach ($this->costCentreRows as $row) {
                $category = Category::whereKey($row['id'])
                    ->where('type', self::TYPES['cost-centre'])
                    ->where('status', 1)
                    ->firstOrFail();
                $category->name = $row['name'];
                $category->save();
            }
        });

        $this->loadRows('cost-centre');
        $this->notify('Cost centres saved.');
    }

    public function requestRemove(string $type, int $id): void
    {
        $this->authoriseSettings();
        $category = $this->activeCategories($type)->whereKey($id)->firstOrFail();

        $this->removeType = $type;
        $this->removeId = $id;
        $this->removeName = $category->name;
        $this->removeAction = $this->isInUse($type, $id) ? 'archive' : 'delete';
        $this->showRemove = true;
    }

    public function removeOption(): void
    {
        $this->authoriseSettings();
        abort_unless($this->removeId && in_array($this->removeAction, ['delete', 'archive'], true), 404);

        $type = $this->removeType;
        $category = $this->activeCategories($type)->whereKey($this->removeId)->firstOrFail();
        $inUse = $this->isInUse($type, $category->id);

        if ($this->removeAction === 'delete' && $inUse) {
            $this->closeModals();
            $this->notify('This option is now in use. Refresh the page and archive it instead.', 'warning');
            return;
        }

        DB::transaction(function () use ($category, $type): void {
            if ($this->removeAction === 'delete') {
                $category->delete();
            } else {
                $category->status = 0;
                $category->save();
            }

            $this->normaliseOrder($type);
        });

        $action = $this->removeAction;
        $this->closeModals();
        $this->loadRows($type);
        $this->notify(
            $action === 'delete' ? 'Option deleted.' : 'Option archived. Existing records were retained.',
            $action === 'delete' ? 'error' : 'warning'
        );
    }

    public function closeModals(): void
    {
        $this->showAddOption = false;
        $this->showRemove = false;
        $this->newOptionName = '';
        $this->newNotifyUsers = [];
        $this->removeType = '';
        $this->removeAction = '';
        $this->removeId = null;
        $this->removeName = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.site.site-notes.settings');
    }

    private function loadCurrentTab(): void
    {
        $this->loadRows($this->tab === 'categories' ? 'category' : 'cost-centre');
    }

    private function loadRows(string $type): void
    {
        $rows = $this->activeCategories($type)
            ->get()
            ->mapWithKeys(function (Category $category) use ($type): array {
                $row = [
                    'id' => $category->id,
                    'name' => $category->name ?? '',
                    'in_use' => $this->isInUse($type, $category->id),
                ];

                if ($type === 'category') {
                    $row['notify_users'] = $this->parseUserIds($category->notify_users);
                }

                return ['row_' . $category->id => $row];
            })
            ->all();

        if ($type === 'category') {
            $this->categoryRows = $rows;
        } else {
            $this->costCentreRows = $rows;
        }
    }

    private function activeCategories(string $type): Builder
    {
        return Category::query()
            ->where('type', $this->categoryType($type))
            ->where('status', 1)
            ->orderBy('order');
    }

    private function categoryType(string $type): string
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        return self::TYPES[$type];
    }

    private function isInUse(string $type, int $id): bool
    {
        $this->categoryType($type);

        return $type === 'category'
            ? SiteNote::where('category_id', $id)->exists()
            : SiteNoteCost::where('cost_id', $id)->exists();
    }

    private function assertRowsAreCurrent(string $type, array $rows): void
    {
        $incomingIds = collect($rows)->pluck('id')->map(fn($id) => (int)$id)->sort()->values();
        $storedIds = $this->activeCategories($type)->pluck('id')->map(fn($id) => (int)$id)->sort()->values();

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

    private function normaliseOrder(string $type): void
    {
        foreach ($this->activeCategories($type)->get() as $index => $category) {
            $category->order = $index + 1;
            $category->save();
        }
    }

    private function parseUserIds(?string $userIds): array
    {
        return collect(explode(',', (string)$userIds))
            ->map(fn($id) => trim($id))
            ->filter()
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();
    }

    private function serialiseUserIds(array $userIds): ?string
    {
        $allowed = $this->staffIds();
        $selected = collect($userIds)
            ->map(fn($id) => (string)$id)
            ->filter(fn($id) => in_array($id, $allowed, true))
            ->unique()
            ->values()
            ->all();

        return $selected ? implode(',', $selected) : null;
    }

    private function staffIds(): array
    {
        return array_map('strval', array_keys($this->staffOptions));
    }

    private function authoriseSettings(): void
    {
        abort_unless(Auth::check() && Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'), 403);
    }

    private function notify(string $message, string $type = 'success'): void
    {
        $this->notifyWithToastr($message, $type);
    }
}
