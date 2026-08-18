<?php

namespace App\Livewire\Site\Foc;

use App\Models\Misc\Category;
use App\Models\Site\SiteFoc;
use App\Models\Site\SiteFocItem;
use App\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Items extends Component
{
    public int $focId;

    public bool $showAddModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingItemId = null;
    public ?int $deletingItemId = null;

    public string $itemName = '';
    public $categoryId = '';

    public string $filter = 'all';
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

        abort_unless(
            Auth::user()->allowed2('edit.site.foc', $foc)
            || Auth::id() == $foc->super_id,
            404
        );

        return $foc;
    }

    protected function deletableFoc(): SiteFoc
    {
        $foc = $this->foc();

        abort_unless(
            Auth::user()->allowed2('del.site.foc', $foc),
            404
        );

        return $foc;
    }

    protected function validCategory(): bool
    {
        $valid = Category::whereKey($this->categoryId)
            ->where('type', 'foc_item')
            ->where('status', 1)
            ->exists();

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
        $this->categoryId = '';
    }

    public function openAdd(): void
    {
        $this->editableFoc();
        $this->resetItemForm();

        $this->showAddModal = true;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function closeModals(): void
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;

        $this->resetItemForm();
    }

    public function saveAdd(): void
    {
        $foc = $this->editableFoc();

        $this->validate([
            'itemName' => ['required', 'string'],
            'categoryId' => ['required', 'integer'],
        ]);

        if (!$this->validCategory()) {
            return;
        }

        $item = SiteFocItem::create([
            'foc_id' => $foc->id,
            'name' => $this->itemName,
            'category_id' => (int) $this->categoryId,
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

        $this->resetValidation();
        $this->editingItemId = $item->id;
        $this->itemName = $item->name;
        $this->categoryId = (string) $item->category_id;

        $this->showAddModal = false;
        $this->showEditModal = true;
        $this->showDeleteModal = false;
    }

    public function saveEdit(): void
    {
        $foc = $this->editableFoc();

        abort_unless($this->editingItemId, 404);

        $this->validate([
            'itemName' => ['required', 'string'],
            'categoryId' => ['required', 'integer'],
        ]);

        if (!$this->validCategory()) {
            return;
        }

        $item = $this->item($this->editingItemId);

        $item->update([
            'name' => $this->itemName,
            'category_id' => (int) $this->categoryId,
        ]);

        // Preserve the existing FOC item-update behaviour.
        $foc->closeToDo();
        $foc->touch();

        $this->showEditModal = false;
        $this->resetItemForm();
        $this->message = 'Item updated.';
    }

    public function markComplete(int $itemId): void
    {
        $foc = $this->editableFoc();
        $item = $this->item($itemId);

        if ((int) $item->status !== 0) {
            $item->update([
                'status' => 0,
                'sign_by' => Auth::id(),
                'sign_at' => now(),
            ]);
        }

        // Preserve the existing controller behaviour.
        $foc->closeToDo();
        $foc->touch();

        $this->message = 'Item marked complete.';
    }

    public function reopen(int $itemId): void
    {
        $foc = $this->editableFoc();
        $item = $this->item($itemId);

        $item->update([
            'status' => 1,
            'sign_by' => null,
            'sign_at' => null,
        ]);

        // Preserve the existing controller behaviour.
        $foc->closeToDo();
        $foc->touch();

        $this->message = 'Item re-opened.';
    }

    public function confirmDelete(int $itemId): void
    {
        $this->deletableFoc();

        $item = $this->item($itemId);

        $this->resetValidation();
        $this->deletingItemId = $item->id;
        $this->itemName = $item->name;

        $this->showAddModal = false;
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
            if ((int) $remainingItem->order !== $order) {
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

        $categories = Category::where('type', 'foc_item')
            ->where('status', 1)
            ->orderBy('order')
            ->get();

        $categoryIds = $categories->pluck('id');

        $itemsQuery = SiteFocItem::where('foc_id', $foc->id)
            ->whereIn('category_id', $categoryIds)
            ->with('category')
            ->orderBy('order');

        if ($this->filter === 'completed') {
            $itemsQuery->where('status', 0);
        } elseif ($this->filter === 'outstanding') {
            $itemsQuery->where('status', 1);
        }

        $items = $itemsQuery->get();

        $signerIds = $items->pluck('sign_by')
            ->filter()
            ->unique()
            ->values();

        $signers = $signerIds->isEmpty()
            ? collect()
            : User::whereIn('id', $signerIds)->get()->keyBy('id');

        $canMutateItems =
            Auth::user()->allowed2('edit.site.foc', $foc)
            || Auth::id() == $foc->super_id;

        $canAdd =
            (bool) $foc->status
            && $canMutateItems
            && Auth::user()->hasAnyRole2(
                'web-admin|mgt-general-manager|con-administrator|con-area-supervisor'
            );

        $canComplete = Auth::user()->allowed2('edit.site.foc', $foc);

        $canEdit =
            (bool) $foc->status
            && $canMutateItems
            && Auth::user()->hasAnyRole2(
                'web-admin|mgt-general-manager|con-administrator'
            );

        $canDelete =
            (bool) $foc->status
            && Auth::user()->allowed2('del.site.foc', $foc)
            && Auth::user()->hasAnyRole2(
                'web-admin|mgt-general-manager'
            );

        return view('livewire.site.foc.items', compact(
            'foc',
            'categories',
            'items',
            'signers',
            'canAdd',
            'canComplete',
            'canEdit',
            'canDelete'
        ));
    }
}
