<?php

namespace App\Livewire\Site\Foc;

use App\Models\Misc\Category;
use App\Models\Site\SiteFoc;
use App\Models\Site\SiteFocItem;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Items extends Component
{
    public int $focId;

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
    public string $message = '';

    public function mount(int $focId): void
    {
        $this->focId = $focId;
    }

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['all', 'completed', 'outstanding'], true)) {
            $this->filter = $filter;
        }
    }

    protected function foc(): SiteFoc
    {
        return SiteFoc::findOrFail($this->focId);
    }

    protected function item(int $itemId): SiteFocItem
    {
        return SiteFocItem::where('foc_id', $this->focId)
            ->findOrFail($itemId);
    }

    protected function editableFoc(): SiteFoc
    {
        $foc = $this->foc();

        abort_unless(Auth::user()->allowed2('edit.site.foc', $foc) || Auth::id() == $foc->super_id, 404);

        return $foc;
    }

    protected function deletableFoc(): SiteFoc
    {
        $foc = $this->foc();

        abort_unless(Auth::user()->allowed2('del.site.foc', $foc), 404);

        return $foc;
    }

    protected function validCategory(): bool
    {
        $valid = Category::whereKey($this->categoryId)->where('type', 'foc_item')->where('status', 1)->exists();

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
        $this->editableFoc();
        $this->resetValidation();
        $this->resetMultipleItems(10);

        $this->showAddModal = false;
        $this->showMultipleModal = true;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function moreItems(): void
    {
        $this->editableFoc();

        for ($i = 0; $i < 5; $i++) {
            $this->multipleItems[] = $this->blankMultipleItem();
        }
    }

    public function saveMultiple(): void
    {
        $foc = $this->editableFoc();

        $this->resetValidation();
        $validCategoryIds = Category::where('type', 'foc_item')->where('status', 1)->pluck('id')->map(fn($id) => (int)$id)->all();

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
            $this->addError('multipleItems', 'Enter at least one FOC item.');
            return;
        }

        $order = $foc->items()->count() + 1;

        foreach ($itemsToCreate as $row) {
            $item = SiteFocItem::create([
                'foc_id' => $foc->id,
                'name' => $row['name'],
                'notes' => $row['notes'],
                'category_id' => $row['category_id'],
                'order' => $order++,
                'status' => 1,
            ]);

            if ($foc->super_id) {
                $item->createAssignSupervisorToDo($foc->super_id);
            }
        }

        $foc->touch();

        $count = count($itemsToCreate);
        $this->showMultipleModal = false;
        $this->multipleItems = [];
        $this->message = $count . ' ' . ($count === 1 ? 'item' : 'items') . ' added.';
    }

    public function openAdd(): void
    {
        $this->editableFoc();
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
        $foc = $this->editableFoc();

        $this->validate([
            'itemName' => ['required', 'string'],
            'itemNotes' => ['nullable', 'string', 'max:5000'],
            'categoryId' => ['required', 'integer'],
        ]);

        if (!$this->validCategory()) {
            return;
        }

        $item = SiteFocItem::create([
            'foc_id' => $foc->id,
            'name' => $this->itemName,
            'notes' => trim($this->itemNotes) ?: null,
            'category_id' => (int)$this->categoryId,
            'order' => $foc->items()->count() + 1,
            'status' => 1,
        ]);

        if ($foc->super_id) {
            $item->createAssignSupervisorToDo($foc->super_id);
        }

        $foc->touch();

        $this->showAddModal = false;
        $this->resetItemForm();
        $this->message = 'Item added.';
    }

    public function openEdit(int $itemId): void
    {
        $this->editableFoc();

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
        $foc = $this->editableFoc();

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
        if ((int) $item->status === SiteFocItem::STATUS_DEFECTIVE
            && strcasecmp(trim($newCategory->name), 'Inspections') !== 0) {
            $updates['status'] = SiteFocItem::STATUS_OUTSTANDING;
        }

        $item->update($updates);

        // Preserve the existing FOC item-update behaviour.
        $foc->closeToDo();
        $foc->touch();

        $this->showEditModal = false;
        $this->resetItemForm();
        $this->message = 'Item updated.';
    }

    public function reorderItems(int $categoryId, array $orderedIds): void
    {
        $foc = $this->editableFoc();

        abort_unless(
            (bool)$foc->status
            && Auth::user()->hasAnyRole2('web-admin|mgt-general-manager|con-administrator'),
            404
        );

        $category = Category::whereKey($categoryId)
            ->where('type', 'foc_item')
            ->where('status', 1)
            ->firstOrFail();

        $categoryItems = SiteFocItem::where('foc_id', $foc->id)
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

        $foc->touch();
    }

    public function setItemStatus(int $itemId, int $status): void
    {
        $foc = $this->editableFoc();
        $item = $this->item($itemId)->load('category');

        abort_unless(in_array($status, [
            SiteFocItem::STATUS_COMPLETED,
            SiteFocItem::STATUS_OUTSTANDING,
            SiteFocItem::STATUS_DEFECTIVE,
        ], true), 422);

        if ($status === SiteFocItem::STATUS_DEFECTIVE) {
            abort_unless($item->isInspections(), 422, 'Defective is only available for Inspections items.');
        }

        $item->update([
            'status' => $status,
            'sign_by' => $status === SiteFocItem::STATUS_COMPLETED ? Auth::id() : null,
            'sign_at' => $status === SiteFocItem::STATUS_COMPLETED ? now() : null,
        ]);

        // Preserve the existing controller behaviour.
        $foc->closeToDo();
        $foc->touch();

        $this->message = match ($status) {
            SiteFocItem::STATUS_COMPLETED => 'Item marked complete.',
            SiteFocItem::STATUS_DEFECTIVE => 'Item marked defective.',
            default => 'Item re-opened.',
        };
    }

    public function saveNotes(int $itemId, ?string $notes): void
    {
        $foc = $this->editableFoc();
        $notes = trim((string) $notes);

        if (mb_strlen($notes) > 5000) {
            $this->addError("notes.$itemId", 'Notes may not exceed 5,000 characters.');
            return;
        }

        $this->item($itemId)->update(['notes' => $notes !== '' ? $notes : null]);
        $foc->touch();
        $this->message = 'Item notes saved.';
    }

    public function confirmDelete(int $itemId): void
    {
        $this->deletableFoc();

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
        $foc = $this->deletableFoc();

        abort_unless($this->deletingItemId, 404);

        $item = $this->item($this->deletingItemId);

        $item->closeToDo();
        $item->delete();

        $order = 1;

        foreach ($foc->items()->orderBy('order')->get() as $remainingItem) {
            if ((int)$remainingItem->order !== $order) {
                $remainingItem->order = $order;
                $remainingItem->save();
            }

            $order++;
        }

        $foc->touch();

        $this->showDeleteModal = false;
        $this->resetItemForm();
        $this->message = 'Item deleted.';
    }

    public function render()
    {
        $foc = $this->foc();

        $categories = Category::where('type', 'foc_item')->where('status', 1)->orderBy('order')->get();
        $categoryIds = $categories->pluck('id');
        $itemsQuery = SiteFocItem::where('foc_id', $foc->id)->whereIn('category_id', $categoryIds)->with('category')->orderBy('order');

        if ($this->filter === 'completed') {
            $itemsQuery->where('status', SiteFocItem::STATUS_COMPLETED);
        } elseif ($this->filter === 'outstanding') {
            $itemsQuery->whereIn('status', [SiteFocItem::STATUS_OUTSTANDING, SiteFocItem::STATUS_DEFECTIVE]);
        }

        $items = $itemsQuery->get();

        $signerIds = $items->pluck('sign_by')->filter()->unique()->values();
        $signers = $signerIds->isEmpty() ? collect() : User::whereIn('id', $signerIds)->get()->keyBy('id');
        $canMutateItems = Auth::user()->allowed2('edit.site.foc', $foc) || Auth::id() == $foc->super_id;

        $canAdd =
            (bool)$foc->status
            && $canMutateItems
            && Auth::user()->hasAnyRole2(
                'web-admin|mgt-general-manager|con-administrator|con-area-supervisor'
            );

        $canComplete = Auth::user()->allowed2('edit.site.foc', $foc);

        $canEdit =
            (bool)$foc->status
            && $canMutateItems
            && Auth::user()->hasAnyRole2(
                'web-admin|mgt-general-manager|con-administrator'
            );

        $canDelete =
            (bool)$foc->status
            && Auth::user()->allowed2('del.site.foc', $foc)
            && Auth::user()->hasAnyRole2(
                'web-admin|mgt-general-manager'
            );

        return view('livewire.site.foc.items', compact(
            'foc',
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
