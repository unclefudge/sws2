<?php

namespace App\Livewire\Safety\Wms;

use App\Models\Safety\WmsControl;
use App\Models\Safety\WmsDoc;
use App\Models\Safety\WmsHazard;
use App\Models\Safety\WmsStep;
use App\Services\FileBank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class Editor extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $docId;

    #[Locked]
    public int $forCompanyId;

    #[Locked]
    public bool $builder;

    #[Locked]
    public bool $master;

    #[Locked]
    public string $companyName = '';

    #[Locked]
    public ?int $parentId = null;

    #[Locked]
    public string $parentName = '';

    #[Locked]
    public int $userCompanyId;

    #[Locked]
    public array $lineage = [];

    public string $name = '';
    public string $project = '';
    public string $principle = '';
    public ?int $principleId = null;
    public ?int $companyId = null;
    public string $version = '1.0';
    public int $status = 2;
    public string $resCompliance = '';
    public string $resReview = '';
    public ?string $attachment = null;
    public ?string $attachmentUrl = null;

    public array $steps = [];

    public bool $modified = false;
    public string $message = '';

    public bool $showItemModal = false;
    public string $itemType = '';
    public ?int $itemStepIndex = null;
    public ?int $itemIndex = null;
    public int $insertAfterStepIndex = -1;
    public string $itemName = '';
    public bool $itemResPrinciple = false;
    public bool $itemResCompany = false;
    public bool $itemResWorker = false;

    public bool $showPrincipalModal = false;
    public bool $showPrincipalConfirmModal = false;
    public string $principalDraft = '';

    public bool $showSignoffModal = false;
    public bool $showIncompleteModal = false;

    public bool $showFileUpload = false;
    public $replacementFile = null;

    public int $newKeyCounter = 1;

    public function mount(int $docId): void
    {
        $this->docId = $docId;

        $doc = WmsDoc::findOrFail($docId);
        abort_unless(Auth::user()->allowed2('edit.wms', $doc), 404);
        abort_unless(in_array((int)$doc->status, [2, 3], true), 404);

        $this->loadDocument($doc);
    }

    protected function loadDocument(WmsDoc $doc): void
    {
        $this->forCompanyId = (int)$doc->for_company_id;
        $this->builder = (bool)$doc->builder;
        $this->master = (bool)$doc->master;
        $this->userCompanyId = (int)Auth::user()->company_id;

        if ($this->master) {
            $this->companyName = 'Company';
            $this->parentId = null;
            $this->parentName = 'Parent Company';
        } else {
            $company = $doc->company()->firstOrFail();
            $parent = $company->reportsTo();

            $this->companyName = $company->name;
            $this->parentId = (int)$parent->id;
            $this->parentName = $parent->name;
        }

        $this->name = (string)$doc->name;
        $this->project = (string)$doc->project;
        $this->principle = (string)$doc->principle;
        $this->principleId = $doc->principle_id ? (int)$doc->principle_id : null;
        $this->companyId = $doc->company_id ? (int)$doc->company_id : null;
        $this->version = (string)$doc->version;
        $this->status = (int)$doc->status;
        $this->resCompliance = (string)($doc->res_compliance ?? '');
        $this->resReview = (string)($doc->res_review ?? '');
        $this->attachment = $doc->attachment;
        $this->attachmentUrl = $doc->attachment
            ? FileBank::url("company/{$doc->for_company_id}/wms/{$doc->attachment}")
            : null;

        $this->lineage = [];
        $this->steps = [];

        $steps = WmsStep::query()
            ->where('doc_id', $doc->id)
            ->orderBy('order')
            ->get();

        foreach ($steps as $step) {
            $stepKey = 'step-' . $step->id;
            $this->lineage[$stepKey] = $this->lineageData(
                (int)$step->master,
                $step->master_id,
                $step->master_id ? WmsStep::find($step->master_id)?->name : null
            );

            $stepData = [
                'key' => $stepKey,
                'name' => (string)$step->name,
                'hazards' => [],
                'controls' => [],
            ];

            foreach (WmsHazard::query()->where('step_id', $step->id)->orderBy('order')->get() as $hazard) {
                $hazardKey = 'hazard-' . $hazard->id;
                $this->lineage[$hazardKey] = $this->lineageData(
                    (int)$hazard->master,
                    $hazard->master_id,
                    $hazard->master_id ? WmsHazard::find($hazard->master_id)?->name : null
                );

                $stepData['hazards'][] = [
                    'key' => $hazardKey,
                    'name' => (string)$hazard->name,
                ];
            }

            foreach (WmsControl::query()->where('step_id', $step->id)->orderBy('order')->get() as $control) {
                $controlKey = 'control-' . $control->id;
                $this->lineage[$controlKey] = $this->lineageData(
                    (int)$control->master,
                    $control->master_id,
                    $control->master_id ? WmsControl::find($control->master_id)?->name : null
                );

                $stepData['controls'][] = [
                    'key' => $controlKey,
                    'name' => (string)$control->name,
                    'res_principle' => (bool)$control->res_principle,
                    'res_company' => (bool)$control->res_company,
                    'res_worker' => (bool)$control->res_worker,
                ];
            }

            $this->steps[] = $stepData;
        }

        $this->modified = false;
    }

    protected function lineageData(int $master, ?int $masterId, ?string $masterName): array
    {
        return [
            'master' => $master,
            'master_id' => $masterId ? (int)$masterId : null,
            'master_name' => $masterName,
        ];
    }

    protected function nextKey(string $type): string
    {
        return 'new-' . $type . '-' . $this->newKeyCounter++;
    }

    public function markModified(): void
    {
        $this->modified = true;
        $this->message = '';
    }

    public function openPrincipal(): void
    {
        abort_if($this->master, 404);

        $this->principalDraft = $this->principle;
        $this->showPrincipalConfirmModal = false;
        $this->showPrincipalModal = true;
    }

    public function savePrincipal(): void
    {
        $this->validate([
            'principalDraft' => ['required', 'string', 'max:100'],
        ]);

        $draft = trim($this->principalDraft);

        if ($this->parentId && $draft === $this->parentName) {
            $this->principle = $draft;
            $this->principleId = $this->parentId;
            $this->companyId = $this->parentId;
            $this->modified = true;
            $this->showPrincipalModal = false;
            return;
        }

        $this->showPrincipalModal = false;
        $this->showPrincipalConfirmModal = true;
    }

    public function confirmOtherPrincipal(): void
    {
        $this->principle = trim($this->principalDraft);
        $this->principleId = null;
        $this->companyId = $this->forCompanyId;
        $this->modified = true;
        $this->showPrincipalConfirmModal = false;
    }

    public function openAddStep(int $afterIndex = -1): void
    {
        $this->resetItemModal();
        $this->itemType = 'step';
        $this->insertAfterStepIndex = $afterIndex;
        $this->showItemModal = true;
    }

    public function openAddHazard(int $stepIndex): void
    {
        abort_unless(isset($this->steps[$stepIndex]), 404);

        $this->resetItemModal();
        $this->itemType = 'hazard';
        $this->itemStepIndex = $stepIndex;
        $this->showItemModal = true;
    }

    public function openAddControl(int $stepIndex): void
    {
        abort_unless(isset($this->steps[$stepIndex]), 404);

        $this->resetItemModal();
        $this->itemType = 'control';
        $this->itemStepIndex = $stepIndex;
        $this->showItemModal = true;
    }

    public function openEditStep(int $stepIndex): void
    {
        abort_unless(isset($this->steps[$stepIndex]), 404);

        $this->resetItemModal();
        $this->itemType = 'step';
        $this->itemStepIndex = $stepIndex;
        $this->itemName = $this->steps[$stepIndex]['name'];
        $this->showItemModal = true;
    }

    public function openEditHazard(int $stepIndex, int $hazardIndex): void
    {
        abort_unless(isset($this->steps[$stepIndex]['hazards'][$hazardIndex]), 404);

        $this->resetItemModal();
        $this->itemType = 'hazard';
        $this->itemStepIndex = $stepIndex;
        $this->itemIndex = $hazardIndex;
        $this->itemName = $this->steps[$stepIndex]['hazards'][$hazardIndex]['name'];
        $this->showItemModal = true;
    }

    public function openEditControl(int $stepIndex, int $controlIndex): void
    {
        abort_unless(isset($this->steps[$stepIndex]['controls'][$controlIndex]), 404);

        $control = $this->steps[$stepIndex]['controls'][$controlIndex];

        $this->resetItemModal();
        $this->itemType = 'control';
        $this->itemStepIndex = $stepIndex;
        $this->itemIndex = $controlIndex;
        $this->itemName = $control['name'];
        $this->itemResPrinciple = (bool)$control['res_principle'];
        $this->itemResCompany = (bool)$control['res_company'];
        $this->itemResWorker = (bool)$control['res_worker'];
        $this->showItemModal = true;
    }

    public function saveItem(): void
    {
        $this->validate([
            'itemName' => ['required', 'string'],
        ]);

        $name = trim($this->itemName);

        if ($this->itemType === 'step') {
            if ($this->itemStepIndex !== null) {
                abort_unless(isset($this->steps[$this->itemStepIndex]), 404);
                $this->steps[$this->itemStepIndex]['name'] = $name;
            } else {
                $newStep = [
                    'key' => $this->nextKey('step'),
                    'name' => $name,
                    'hazards' => [],
                    'controls' => [],
                ];

                $position = max(0, min(count($this->steps), $this->insertAfterStepIndex + 1));
                array_splice($this->steps, $position, 0, [$newStep]);
            }
        } elseif ($this->itemType === 'hazard') {
            abort_unless($this->itemStepIndex !== null && isset($this->steps[$this->itemStepIndex]), 404);

            if ($this->itemIndex !== null) {
                abort_unless(isset($this->steps[$this->itemStepIndex]['hazards'][$this->itemIndex]), 404);
                $this->steps[$this->itemStepIndex]['hazards'][$this->itemIndex]['name'] = $name;
            } else {
                $this->steps[$this->itemStepIndex]['hazards'][] = [
                    'key' => $this->nextKey('hazard'),
                    'name' => $name,
                ];
            }
        } elseif ($this->itemType === 'control') {
            abort_unless($this->itemStepIndex !== null && isset($this->steps[$this->itemStepIndex]), 404);

            $data = [
                'name' => $name,
                'res_principle' => $this->itemResPrinciple,
                'res_company' => $this->itemResCompany,
                'res_worker' => $this->itemResWorker,
            ];

            if ($this->itemIndex !== null) {
                abort_unless(isset($this->steps[$this->itemStepIndex]['controls'][$this->itemIndex]), 404);
                $key = $this->steps[$this->itemStepIndex]['controls'][$this->itemIndex]['key'];
                $this->steps[$this->itemStepIndex]['controls'][$this->itemIndex] = ['key' => $key] + $data;
            } else {
                $this->steps[$this->itemStepIndex]['controls'][] = ['key' => $this->nextKey('control')] + $data;
            }
        } else {
            abort(404);
        }

        $this->modified = true;
        $this->closeItemModal();
    }

    public function deleteStep(int $stepIndex): void
    {
        abort_unless(isset($this->steps[$stepIndex]), 404);

        array_splice($this->steps, $stepIndex, 1);
        $this->modified = true;
    }

    public function deleteHazard(int $stepIndex, int $hazardIndex): void
    {
        abort_unless(isset($this->steps[$stepIndex]['hazards'][$hazardIndex]), 404);

        array_splice($this->steps[$stepIndex]['hazards'], $hazardIndex, 1);
        $this->modified = true;
    }

    public function deleteControl(int $stepIndex, int $controlIndex): void
    {
        abort_unless(isset($this->steps[$stepIndex]['controls'][$controlIndex]), 404);

        array_splice($this->steps[$stepIndex]['controls'], $controlIndex, 1);
        $this->modified = true;
    }

    public function moveStep(int $stepIndex, int $direction): void
    {
        $this->moveArrayItem($this->steps, $stepIndex, $direction);
    }

    public function moveHazard(int $stepIndex, int $hazardIndex, int $direction): void
    {
        abort_unless(isset($this->steps[$stepIndex]), 404);

        $items = $this->steps[$stepIndex]['hazards'];
        $this->moveArrayItem($items, $hazardIndex, $direction);
        $this->steps[$stepIndex]['hazards'] = $items;
    }

    public function moveControl(int $stepIndex, int $controlIndex, int $direction): void
    {
        abort_unless(isset($this->steps[$stepIndex]), 404);

        $items = $this->steps[$stepIndex]['controls'];
        $this->moveArrayItem($items, $controlIndex, $direction);
        $this->steps[$stepIndex]['controls'] = $items;
    }

    protected function moveArrayItem(array &$items, int $index, int $direction): void
    {
        abort_unless(isset($items[$index]), 404);
        abort_unless(in_array($direction, [-1, 1], true), 422);

        $newIndex = $index + $direction;

        if ($newIndex < 0 || $newIndex >= count($items)) {
            return;
        }

        [$items[$index], $items[$newIndex]] = [$items[$newIndex], $items[$index]];
        $items = array_values($items);
        $this->modified = true;
    }

    public function saveDraft(): void
    {
        $this->persist(false);
        $this->message = 'Saved Document.';
    }

    public function makeActive()
    {
        abort_unless($this->master && Auth::user()->hasPermission2('edit.wms'), 404);

        $this->persist(true);

        return redirect('/safety/doc/wms/' . $this->docId);
    }

    protected function persist(bool $makeActive): void
    {
        $this->validateDocument();

        DB::transaction(function () use ($makeActive) {
            $doc = WmsDoc::query()->whereKey($this->docId)->lockForUpdate()->firstOrFail();
            abort_unless(Auth::user()->allowed2('edit.wms', $doc), 404);

            $newVersion = $this->incrementMinorVersion((string)$doc->version);

            $doc->update([
                'name' => trim($this->name),
                'project' => trim($this->project),
                'principle' => trim($this->principle),
                'principle_id' => $this->principleId,
                'company_id' => $this->companyId,
                'res_compliance' => trim($this->resCompliance) ?: null,
                'res_review' => trim($this->resReview) ?: null,
                'status' => $makeActive ? 1 : $this->status,
                'version' => $newVersion,
            ]);

            foreach ($doc->steps as $step) {
                WmsHazard::where('step_id', $step->id)->delete();
                WmsControl::where('step_id', $step->id)->delete();
            }

            WmsStep::where('doc_id', $doc->id)->delete();

            foreach ($this->steps as $stepOrder => $stepData) {
                $stepLineage = $this->lineage[$stepData['key']] ?? null;

                $newStep = WmsStep::create([
                    'doc_id' => $doc->id,
                    'name' => trim($stepData['name']),
                    'order' => $stepOrder + 1,
                    'master' => $stepLineage['master'] ?? ($this->master ? 1 : 0),
                    'master_id' => $stepLineage['master_id'] ?? null,
                ]);

                foreach ($stepData['hazards'] as $hazardOrder => $hazardData) {
                    $hazardLineage = $this->lineage[$hazardData['key']] ?? null;

                    WmsHazard::create([
                        'step_id' => $newStep->id,
                        'name' => trim($hazardData['name']),
                        'order' => $hazardOrder + 1,
                        'master' => $hazardLineage['master'] ?? ($this->master ? 1 : 0),
                        'master_id' => $hazardLineage['master_id'] ?? null,
                    ]);
                }

                foreach ($stepData['controls'] as $controlOrder => $controlData) {
                    $controlLineage = $this->lineage[$controlData['key']] ?? null;

                    WmsControl::create([
                        'step_id' => $newStep->id,
                        'name' => trim($controlData['name']),
                        'order' => $controlOrder + 1,
                        'res_principle' => (int)$controlData['res_principle'],
                        'res_company' => (int)$controlData['res_company'],
                        'res_worker' => (int)$controlData['res_worker'],
                        'master' => $controlLineage['master'] ?? ($this->master ? 1 : 0),
                        'master_id' => $controlLineage['master_id'] ?? null,
                    ]);
                }
            }

            $this->version = $newVersion;
            $this->status = (int)$doc->status;
        });

        $doc = WmsDoc::findOrFail($this->docId);
        $this->loadDocument($doc);
    }

    protected function validateDocument(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'project' => ['nullable', 'string', 'max:100'],
            'principle' => ['nullable', 'string', 'max:100'],
            'resCompliance' => ['nullable', 'string'],
            'resReview' => ['nullable', 'string'],
            'steps' => ['array'],
            'steps.*.name' => ['required_if:builder,1', 'string'],
            'steps.*.hazards' => ['array'],
            'steps.*.hazards.*.name' => ['required', 'string'],
            'steps.*.controls' => ['array'],
            'steps.*.controls.*.name' => ['required', 'string'],
            'steps.*.controls.*.res_principle' => ['boolean'],
            'steps.*.controls.*.res_company' => ['boolean'],
            'steps.*.controls.*.res_worker' => ['boolean'],
        ]);
    }

    protected function incrementMinorVersion(string $version): string
    {
        $parts = explode('.', $version, 2);
        $major = (int)($parts[0] ?? 1);
        $minor = (int)($parts[1] ?? 0);

        return $major . '.' . ($minor + 1);
    }

    public function prepareSignoff(): void
    {
        if (!$this->isComplete()) {
            $this->showIncompleteModal = true;
            return;
        }

        if ($this->modified) {
            $this->persist(false);
        }

        $this->showSignoffModal = true;
    }

    public function startFileChange(): void
    {
        abort_if($this->builder, 404);

        $this->resetValidation('replacementFile');
        $this->replacementFile = null;
        $this->showFileUpload = true;
    }

    public function cancelFileChange(): void
    {
        $this->replacementFile = null;
        $this->showFileUpload = false;
        $this->resetValidation('replacementFile');
    }

    public function uploadReplacement(): void
    {
        abort_if($this->builder, 404);

        $this->validateDocument();
        $this->validate([
            'replacementFile' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $doc = WmsDoc::findOrFail($this->docId);
        abort_unless(Auth::user()->allowed2('edit.wms', $doc), 404);

        $newVersion = $this->incrementMinorVersion((string)$doc->version);
        $extension = strtolower($this->replacementFile->getClientOriginalExtension());
        $filename = sanitizeFilename($this->name) . '-v' . $newVersion . '-ref-' . $doc->id . '.' . $extension;
        $basePath = 'company/' . $doc->for_company_id . '/wms';

        $stored = FileBank::storeUploadedFile($this->replacementFile, $basePath, $filename);
        $oldAttachment = $doc->attachment;

        DB::transaction(function () use ($doc, $newVersion, $stored) {
            $locked = WmsDoc::query()->whereKey($doc->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'name' => trim($this->name),
                'project' => trim($this->project),
                'principle' => trim($this->principle),
                'principle_id' => $this->principleId,
                'company_id' => $this->companyId,
                'res_compliance' => trim($this->resCompliance) ?: null,
                'res_review' => trim($this->resReview) ?: null,
                'version' => $newVersion,
                'attachment' => $stored,
            ]);
        });

        if ($oldAttachment && $oldAttachment !== $stored) {
            FileBank::delete($basePath . '/' . $oldAttachment);
        }

        $this->replacementFile = null;
        $this->showFileUpload = false;
        $this->message = 'PDF replaced.';

        $this->loadDocument(WmsDoc::findOrFail($this->docId));
    }

    public function itemDifferent(array $item): bool
    {
        if ($this->status !== 3 || !$this->canSeePendingDifferences()) {
            return false;
        }

        $lineage = $this->lineage[$item['key']] ?? null;

        if (!$lineage || !$lineage['master_id']) {
            return true;
        }

        return trim((string)$item['name']) !== trim((string)$lineage['master_name']);
    }

    public function responsibleName(array $control): string
    {
        $parts = [];

        if (!empty($control['res_principle'])) {
            $parts[] = 'Principal Contractor';
        }

        if (!empty($control['res_company'])) {
            $parts[] = $this->companyName;
        }

        if (!empty($control['res_worker'])) {
            $parts[] = 'Worker';
        }

        return implode(' & ', $parts);
    }

    public function isComplete(): bool
    {
        return trim($this->resCompliance) !== '' && trim($this->resReview) !== '';
    }

    protected function canSeePendingDifferences(): bool
    {
        if (!$this->principleId) {
            return true;
        }

        $doc = WmsDoc::find($this->docId);

        return $doc ? Auth::user()->allowed2('sig.wms', $doc) : false;
    }

    protected function resetItemModal(): void
    {
        $this->resetValidation('itemName');
        $this->itemType = '';
        $this->itemStepIndex = null;
        $this->itemIndex = null;
        $this->insertAfterStepIndex = -1;
        $this->itemName = '';
        $this->itemResPrinciple = false;
        $this->itemResCompany = false;
        $this->itemResWorker = false;
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetItemModal();
    }

    public function closePrincipalModal(): void
    {
        $this->showPrincipalModal = false;
        $this->showPrincipalConfirmModal = false;
        $this->principalDraft = '';
        $this->resetValidation('principalDraft');
    }

    public function closeSignoffModal(): void
    {
        $this->showSignoffModal = false;
        $this->showIncompleteModal = false;
    }

    public function render()
    {
        $doc = WmsDoc::findOrFail($this->docId);

        $signoffMode = 'none';

        if (!$this->master) {
            if (!$this->principleId) {
                $signoffMode = 'manual';
            } elseif (Auth::user()->allowed2('sig.wms', $doc)) {
                $signoffMode = 'principle';
            } else {
                $signoffMode = 'request';
            }
        }

        return view('livewire.safety.wms.editor', [
            'canDelete' => Auth::user()->allowed2('del.wms', $doc),
            'canMakeActive' => $this->master && Auth::user()->hasPermission2('edit.wms'),
            'signoffMode' => $signoffMode,
            'complete' => $this->isComplete(),
            'updatedBy' => $doc->displayUpdatedBy(),
        ]);
    }
}
