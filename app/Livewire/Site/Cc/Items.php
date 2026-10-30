<?php

namespace App\Livewire\Site\Cc;

use App\Livewire\Concerns\NotifiesWithToastr;
use App\Models\Misc\Category;
use App\Models\Site\SiteCc;
use App\Models\Site\SiteCcItem;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Items extends Component
{
    use NotifiesWithToastr;

    #[\Livewire\Attributes\Locked]
    public int $ccId;

    public bool $showAddModal = false;
    public bool $showMultipleModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingItemId = null;
    public ?int $deletingItemId = null;

    public string $itemName = '';
    public string $itemNotes = '';
    public $categoryId = '';

    public string $filter = 'all';
    public array $multipleItems = [];

    public function mount(int $ccId): void
    {
        $this->ccId = $ccId;
    }

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['all', 'completed', 'outstanding'], true)) {
            $this->filter = $filter;
        }
    }

    protected function cc(): SiteCc
    {
        $cc = SiteCc::findOrFail($this->ccId);
        abort_unless(Auth::check() && Auth::user()->allowed2('view.site.cc', $cc), 403);
        return $cc;
    }

    protected function item(int $itemId): SiteCcItem
    {
        return SiteCcItem::where('cc_id', $this->ccId)
            ->findOrFail($itemId);
    }

    protected function editableCc(): SiteCc
    {
        $cc = $this->cc();

        abort_unless(Auth::user()->allowed2('edit.site.cc', $cc), 404);

        return $cc;
    }

    protected function deletableCc(): SiteCc
    {
        $cc = $this->cc();

        abort_unless(Auth::user()->allowed2('del.site.cc', $cc), 404);

        return $cc;
    }

    protected function validCategory(): bool
    {
        $valid = Category::whereKey($this->categoryId)->where('type', 'cc_item')->where('status', 1)->exists();

        if (!$valid) {
            $this->addError('categoryId', 'Please select a valid category.');
        }

        return $valid;
    }

    protected function resetItemForm(): void
    {
        $this->resetValidation();

        $this->editingItemId = null;
        $this->deletingItemId = null;
        $this->itemName = '';
        $this->itemNotes = '';
        $this->categoryId = '';
    }

    protected function blankMultipleItem(): array
    {
        return ['category_id' => '', 'name' => '', 'notes' => ''];
    }

    protected function resetMultipleItems(int $count = 10): void
    {
        $this->multipleItems = [];

        for ($i = 0; $i < $count; $i++) {
            $this->multipleItems[] = $this->blankMultipleItem();
        }
    }

    public function openMultiple(): void
    {
        $this->editableCc();
        $this->resetValidation();
        $this->resetMultipleItems(10);

        $this->showAddModal = false;
        $this->showMultipleModal = true;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function moreItems(): void
    {
        $this->editableCc();

        for ($i = 0; $i < 5; $i++) {
            $this->multipleItems[] = $this->blankMultipleItem();
        }
    }

    public function saveMultiple(): void
    {
        $cc = $this->editableCc();

        $this->resetValidation();
        $validCategoryIds = Category::where('type', 'cc_item')->where('status', 1)->pluck('id')->map(fn($id) => (int)$id)->all();

        $itemsToCreate = [];

        foreach ($this->multipleItems as $index => $row) {
            $name = trim((string)($row['name'] ?? ''));
            $notes = trim((string)($row['notes'] ?? ''));
            $categoryId = $row['category_id'] ?? '';

            // Completely blank rows are ignored.
            if ($name === '' && $categoryId === '') {
                continue;
            }

            if ($name === '') {
                $this->addError("multipleItems.$index.name", 'Item description is required.');
            }

            if ($categoryId === '' || !in_array((int)$categoryId, $validCategoryIds, true)) {
                $this->addError("multipleItems.$index.category_id", 'Please select a category.');
            }

            if ($name !== '' && $categoryId !== '' && in_array((int)$categoryId, $validCategoryIds, true)) {
                $itemsToCreate[] = [
                    'name' => $name,
                    'notes' => $notes !== '' ? $notes : null,
                    'category_id' => (int)$categoryId,
                ];
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        if (empty($itemsToCreate)) {
            $this->addError('multipleItems', 'Enter at least one CC item.');
            return;
        }

        $order = $cc->items()->count() + 1;

        foreach ($itemsToCreate as $row) {
            $item = SiteCcItem::create([
                'cc_id' => $cc->id,
                'name' => $row['name'],
                'notes' => $row['notes'],
                'category_id' => $row['category_id'],
                'order' => $order++,
                'status' => 1,
            ]);

            if ($cc->super_id) {
                $item->createAssignSupervisorToDo($cc->super_id);
            }
        }

        $cc->touch();

        $count = count($itemsToCreate);
        $this->showMultipleModal = false;
        $this->multipleItems = [];
        $this->notify($count . ' ' . ($count === 1 ? 'item' : 'items') . ' added.');
    }

    public function openAdd(): void
    {
        $this->editableCc();
        $this->resetItemForm();

        $this->showAddModal = true;
        $this->showMultipleModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function closeModals(): void
    {
        $this->showAddModal = false;
        $this->showMultipleModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->multipleItems = [];

        $this->resetItemForm();
    }

    public function saveAdd(): void
    {
        $cc = $this->editableCc();

        $this->validate([
            'itemName' => ['required', 'string'],
            'itemNotes' => ['nullable', 'string', 'max:5000'],
            'categoryId' => ['required', 'integer'],
        ]);

        if (!$this->validCategory()) {
            return;
        }

        $item = SiteCcItem::create([
            'cc_id' => $cc->id,
            'name' => $this->itemName,
            'notes' => trim($this->itemNotes) ?: null,
            'category_id' => (int)$this->categoryId,
            'order' => $cc->items()->count() + 1,
            'status' => 1,
        ]);

        if ($cc->super_id) {
            $item->createAssignSupervisorToDo($cc->super_id);
        }

        $cc->touch();

        $this->showAddModal = false;
        $this->resetItemForm();
        $this->notify('Item added.');
    }

    public function openEdit(int $itemId): void
    {
        $this->editableCc();

        $item = $this->item($itemId);

        abort_if($item->sign_by, 404);

        $this->resetValidation();
        $this->editingItemId = $item->id;
        $this->itemName = $item->name;
        $this->itemNotes = (string) $item->notes;
        $this->categoryId = (string)$item->category_id;

        $this->showAddModal = false;
        $this->showMultipleModal = false;
        $this->showEditModal = true;
        $this->showDeleteModal = false;
    }

    public function saveEdit(): void
    {
        $cc = $this->editableCc();

        abort_unless($this->editingItemId, 404);

        $this->validate([
            'itemName' => ['required', 'string'],
            'itemNotes' => ['nullable', 'string', 'max:5000'],
            'categoryId' => ['required', 'integer'],
        ]);

        if (!$this->validCategory()) {
            return;
        }

        $item = $this->item($this->editingItemId);

        abort_if($item->sign_by, 404);

        $newCategoryId = (int) $this->categoryId;
        $updates = [
            'name' => $this->itemName,
            'notes' => trim($this->itemNotes) ?: null,
            'category_id' => $newCategoryId,
        ];

        // Defective is only valid for the Inspections category. Re-open the
        // item if an administrator moves it to another category.
        $newCategory = Category::findOrFail($newCategoryId);
        if ((int) $item->status === SiteCcItem::STATUS_DEFECTIVE
            && strcasecmp(trim($newCategory->name), 'Inspections') !== 0) {
            $updates['status'] = SiteCcItem::STATUS_OUTSTANDING;
        }

        $item->update($updates);

        // Preserve the existing CC item-update behaviour.
        $cc->closeToDo();
        $cc->touch();

        $this->showEditModal = false;
        $this->resetItemForm();
        $this->notify('Item updated.');
    }

    public function reorderItems(int $categoryId, array $orderedIds): void
    {
        $cc = $this->editableCc();

        abort_unless(
            (bool)$cc->status
            && Auth::user()->hasPermission2('edit.site.cc'),
            404
        );

        $category = Category::whereKey($categoryId)
            ->where('type', 'cc_item')
            ->where('status', 1)
            ->firstOrFail();

        $categoryItems = SiteCcItem::where('cc_id', $cc->id)
            ->where('category_id', $category->id)
            ->orderBy('order')
            ->get();

        $existingIds = $categoryItems->pluck('id')->map(fn($id) => (int)$id)->all();
        $orderedIds = array_map('intval', $orderedIds);

        $existingSorted = $existingIds;
        $orderedSorted = $orderedIds;
        sort($existingSorted);
        sort($orderedSorted);

        abort_unless($existingSorted === $orderedSorted, 422);

        // Keep this category's existing order slots so reordering one category
        // does not disturb the relative order values used by other categories.
        $orderSlots = $categoryItems->pluck('order')->values()->all();
        $itemsById = $categoryItems->keyBy('id');

        DB::transaction(function () use ($orderedIds, $orderSlots, $itemsById) {
            foreach ($orderedIds as $index => $itemId) {
                $itemsById->get($itemId)->update(['order' => $orderSlots[$index]]);
            }
        });

        $cc->touch();
        $this->notify('Item order updated.');
    }

    public function setItemStatus(int $itemId, int $status): void
    {
        $cc = $this->editableCc();
        $item = $this->item($itemId)->load('category');

        abort_unless(in_array($status, [
            SiteCcItem::STATUS_COMPLETED,
            SiteCcItem::STATUS_OUTSTANDING,
            SiteCcItem::STATUS_DEFECTIVE,
        ], true), 422);

        if ($status === SiteCcItem::STATUS_DEFECTIVE) {
            abort_unless($item->isInspections(), 422, 'Defective is only available for Inspections items.');
        }

        $item->update([
            'status' => $status,
            'sign_by' => $status === SiteCcItem::STATUS_COMPLETED ? Auth::id() : null,
            'sign_at' => $status === SiteCcItem::STATUS_COMPLETED ? now() : null,
        ]);

        // Preserve the existing controller behaviour.
        $cc->closeToDo();
        $cc->touch();

        $message = match ($status) {
            SiteCcItem::STATUS_COMPLETED => 'Item marked complete.',
            SiteCcItem::STATUS_DEFECTIVE => 'Item marked defective.',
            default => 'Item re-opened.',
        };

        $this->notify($message, $status === SiteCcItem::STATUS_DEFECTIVE ? 'warning' : 'success');
    }

    public function saveNotes(int $itemId, ?string $notes): void
    {
        $cc = $this->editableCc();
        $notes = trim((string) $notes);

        if (mb_strlen($notes) > 5000) {
            $this->addError("notes.$itemId", 'Notes may not exceed 5,000 characters.');
            return;
        }

        $this->item($itemId)->update(['notes' => $notes !== '' ? $notes : null]);
        $cc->touch();
        $this->notify('Item notes saved.');
    }

    public function confirmDelete(int $itemId): void
    {
        $this->deletableCc();

        $item = $this->item($itemId);

        $this->resetValidation();
        $this->deletingItemId = $item->id;
        $this->itemName = $item->name;

        $this->showAddModal = false;
        $this->showMultipleModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        $cc = $this->deletableCc();

        abort_unless($this->deletingItemId, 404);

        $item = $this->item($this->deletingItemId);

        $item->closeToDo();
        $item->delete();

        $order = 1;

        foreach ($cc->items()->orderBy('order')->get() as $remainingItem) {
            if ((int)$remainingItem->order !== $order) {
                $remainingItem->order = $order;
                $remainingItem->save();
            }

            $order++;
        }

        $cc->touch();

        $this->showDeleteModal = false;
        $this->resetItemForm();
        $this->notify('Item deleted.', 'error');
    }

    public function render()
    {
        $cc = $this->cc();

        $categories = Category::where('type', 'cc_item')->where('status', 1)->orderBy('order')->get();
        $categoryIds = $categories->pluck('id');
        $itemsQuery = SiteCcItem::where('cc_id', $cc->id)->whereIn('category_id', $categoryIds)->with('category')->orderBy('order');

        if ($this->filter === 'completed') {
            $itemsQuery->where('status', SiteCcItem::STATUS_COMPLETED);
        } elseif ($this->filter === 'outstanding') {
            $itemsQuery->whereIn('status', [SiteCcItem::STATUS_OUTSTANDING, SiteCcItem::STATUS_DEFECTIVE]);
        }

        $items = $itemsQuery->get();

        $signerIds = $items->pluck('sign_by')->filter()->unique()->values();
        $signers = $signerIds->isEmpty() ? collect() : User::whereIn('id', $signerIds)->get()->keyBy('id');
        $canMutateItems = Auth::user()->allowed2('edit.site.cc', $cc);

        $canAdd =
            (bool)$cc->status
            && $canMutateItems
            && Auth::user()->hasPermission2('edit.site.cc');

        $canComplete = Auth::user()->allowed2('edit.site.cc', $cc);

        $canEdit =
            (bool)$cc->status
            && $canMutateItems
            && Auth::user()->hasPermission2('edit.site.cc');

        $canDelete =
            (bool)$cc->status
            && Auth::user()->allowed2('del.site.cc', $cc)
            && Auth::user()->hasPermission2('edit.site.cc');

        return view('livewire.site.cc.items', compact(
            'cc',
            'categories',
            'items',
            'signers',
            'canMutateItems',
            'canAdd',
            'canComplete',
            'canEdit',
            'canDelete'
        ));
    }
}
