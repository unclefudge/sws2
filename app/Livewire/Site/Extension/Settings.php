<?php

namespace App\Livewire\Site\Extension;

use App\Livewire\Concerns\NotifiesWithToastr;
use App\Models\Misc\Category;
use App\Models\Site\SiteExtensionSite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Settings extends Component
{
    use NotifiesWithToastr;

    public array $rows = [];

    public bool $showAdd = false;
    public string $newName = '';

    public bool $showRemove = false;
    public ?int $removeId = null;
    public string $removeName = '';
    public string $removeAction = '';

    public ?string $warningMessage = null;

    public function mount(): void
    {
        $this->authoriseSettings();
        $this->loadRows();
    }

    public function openAdd(): void
    {
        $this->authoriseSettings();
        $this->resetValidation();
        $this->newName = '';
        $this->showAdd = true;
    }

    public function addReason(): void
    {
        $this->authoriseSettings();
        $this->validate([
            'newName' => ['required', 'string', 'max:255'],
        ], [], [
            'newName' => 'reason name',
        ]);

        Category::create([
            'type' => 'site_extension',
            'name' => trim($this->newName),
            'order' => ((int) Category::where('type', 'site_extension')->max('order')) + 1,
            'company_id' => Auth::user()->company->reportsTo()->id,
            'status' => 1,
        ]);

        $this->closeModals();
        $this->loadRows();
        $this->notify('Extension reason added.');
    }

    public function reorderReasons(array $orderedIds): void
    {
        $this->authoriseSettings();
        $this->rows = $this->reorderRows($this->rows, $orderedIds);

        DB::transaction(function (): void {
            $this->assertRowsAreCurrent();

            foreach (array_values($this->rows) as $index => $row) {
                Category::whereKey($row['id'])
                    ->where('type', 'site_extension')
                    ->where('status', 1)
                    ->update([
                        'order' => $index + 1,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }
        });

        $this->notify('Extension reason order saved.');
        $this->skipRender();
    }

    public function saveReasons(): void
    {
        $this->authoriseSettings();
        $this->validate([
            'rows.*.name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function (): void {
            $this->assertRowsAreCurrent();

            foreach ($this->rows as $row) {
                $category = Category::whereKey($row['id'])
                    ->where('type', 'site_extension')
                    ->where('status', 1)
                    ->firstOrFail();

                abort_if(
                    $this->isSystemReason($category) && trim($row['name']) !== $category->name,
                    422,
                    $category->name . ' is a required system reason and cannot be renamed.'
                );

                $category->update(['name' => trim($row['name'])]);
            }
        });

        $this->loadRows();
        $this->notify('Extension reasons saved.');
    }

    public function requestRemove(int $id): void
    {
        $this->authoriseSettings();
        $this->warningMessage = null;

        $category = Category::whereKey($id)
            ->where('type', 'site_extension')
            ->where('status', 1)
            ->firstOrFail();

        if ($this->isSystemReason($category)) {
            $this->warningMessage = $category->name . ' is a required system reason and cannot be removed.';
            return;
        }

        $this->removeId = $category->id;
        $this->removeName = $category->name;
        $this->removeAction = $this->isInUse($category->id) ? 'archive' : 'delete';
        $this->showRemove = true;
    }

    public function removeReason(): void
    {
        $this->authoriseSettings();
        abort_unless($this->removeId && in_array($this->removeAction, ['delete', 'archive'], true), 404);

        $category = Category::whereKey($this->removeId)
            ->where('type', 'site_extension')
            ->where('status', 1)
            ->firstOrFail();

        abort_if($this->isSystemReason($category), 409, 'This system reason cannot be removed.');

        $inUse = $this->isInUse($category->id);
        if ($this->removeAction === 'delete') {
            if ($inUse) {
                $this->closeModals();
                $this->warningMessage = 'This reason is now in use. Refresh the page and archive it instead.';
                return;
            }
            $category->delete();
        } else {
            $category->update(['status' => 0]);
        }

        $action = $this->removeAction;
        $this->closeModals();
        $this->loadRows();
        $this->notify(
            $action === 'delete' ? 'Extension reason deleted.' : 'Extension reason archived.',
            $action === 'delete' ? 'error' : 'warning'
        );
    }

    public function closeModals(): void
    {
        $this->showAdd = false;
        $this->showRemove = false;
        $this->removeId = null;
        $this->removeName = '';
        $this->removeAction = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.site.extension.settings');
    }

    private function loadRows(): void
    {
        $this->rows = Category::where('type', 'site_extension')
            ->where('status', 1)
            ->orderBy('order')
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                'row_' . $category->id => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'in_use' => $this->isInUse($category->id),
                    'locked' => $this->isSystemReason($category),
                ],
            ])->all();
    }

    private function isInUse(int $categoryId): bool
    {
        return SiteExtensionSite::whereRaw('FIND_IN_SET(?, reasons)', [$categoryId])->exists();
    }

    private function isSystemReason(Category $category): bool
    {
        return in_array($category->name, ['N/A', 'Public Holiday'], true);
    }

    private function assertRowsAreCurrent(): void
    {
        $incomingIds = collect($this->rows)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
        $storedIds = Category::where('type', 'site_extension')->where('status', 1)
            ->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();

        abort_unless($incomingIds->all() === $storedIds->all(), 409, 'The extension reasons changed. Refresh and try again.');
    }

    private function reorderRows(array $rows, array $orderedIds): array
    {
        $orderedIds = array_map('intval', $orderedIds);
        $currentIds = array_map(fn (array $row) => (int) $row['id'], array_values($rows));
        $submittedIds = $orderedIds;
        sort($currentIds);
        sort($submittedIds);

        abort_unless($currentIds === $submittedIds, 422, 'The reason order is invalid. Refresh and try again.');

        $ordered = [];
        foreach ($orderedIds as $id) {
            $key = 'row_' . $id;
            $ordered[$key] = $rows[$key];
        }

        return $ordered;
    }

    private function authoriseSettings(): void
    {
        abort_unless(Auth::check() && Auth::user()->hasPermission2('del.site.extension'), 403);
    }
}
