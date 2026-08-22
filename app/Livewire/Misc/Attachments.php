<?php

namespace App\Livewire\Misc;

use App\Models\Misc\Attachment;
use App\Models\Site\SiteFoc;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteNote;
use App\Models\Site\SitePracCompletion;
use App\Models\Site\SiteScaffoldHandover;
use App\Services\FileBank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class Attachments extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $context;

    #[Locked]
    public int $contextId;

    public $upload = null;
    public bool $showUploader = false;
    public bool $showDeleteModal = false;
    public ?int $deleteAttachmentId = null;
    public string $deleteAttachmentName = '';
    public bool $showViewer = false;
    public ?int $viewerAttachmentId = null;
    public string $message = '';

    public function mount(string $context, int $contextId): void
    {
        $this->context = $context;
        $this->contextId = $contextId;

        abort_unless($this->definition(), 404);
        abort_unless($record = $this->record(), 404);
        abort_unless($this->canView($record), 403);
    }

    public function toggleUploader(): void
    {
        abort_unless($this->canUpload(), 403);

        $this->resetValidation();
        $this->message = '';
        $this->showUploader = !$this->showUploader;

        if (!$this->showUploader)
            $this->upload = null;
    }

    public function storeUpload(): void
    {
        abort_unless($this->canUpload(), 403);

        $this->validate([
            'upload' => ['required', 'file', 'max:20480'],
        ], [
            'upload.max' => 'Each attachment must be 20 MB or smaller.',
        ]);

        $record = $this->record();
        abort_unless($record, 404);

        $directory = $this->directory($record);
        $extension = strtolower($this->upload->getClientOriginalExtension());
        $isImage = in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'], true);
        $originalName = $this->upload->getClientOriginalName();
        $filename = FileBank::storeUploadedFile($this->upload, $directory, null, $isImage);

        try {
            Attachment::create([
                'table' => $this->definition()['table'],
                'table_id' => $record->getKey(),
                'directory' => $directory,
                'attachment' => $filename,
                'name' => $originalName,
                'type' => $isImage ? 'image' : 'file',
            ]);
        } catch (\Throwable $e) {
            FileBank::delete($directory . '/' . $filename);
            throw $e;
        }

        $record->touch();
        $this->upload = null;
        $this->message = 'Attachment uploaded.';
        $this->dispatch('attachments-stored');
    }

    public function confirmDelete(int $attachmentId): void
    {
        abort_unless($this->canDelete(), 403);

        $attachment = $this->attachmentQuery()->findOrFail($attachmentId);
        $this->deleteAttachmentId = $attachment->id;
        $this->deleteAttachmentName = $attachment->name ?: $attachment->attachment ?: "Attachment #{$attachment->id}";
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteAttachmentId = null;
        $this->deleteAttachmentName = '';
    }

    public function deleteAttachment(): void
    {
        abort_unless($this->canDelete(), 403);
        abort_unless($this->deleteAttachmentId, 404);

        $attachment = $this->attachmentQuery()->findOrFail($this->deleteAttachmentId);
        $wasOpen = $this->viewerAttachmentId === $attachment->id;
        $attachment->delete();
        $this->record()?->touch();
        $this->closeDeleteModal();

        if ($wasOpen)
            $this->closeViewer();

        $this->message = 'Attachment deleted.';
    }

    public function openViewer(int $attachmentId): void
    {
        $attachment = $this->imageQuery()->findOrFail($attachmentId);
        $this->viewerAttachmentId = $attachment->id;
        $this->showViewer = true;
    }

    public function closeViewer(): void
    {
        $this->showViewer = false;
        $this->viewerAttachmentId = null;
    }

    public function previousImage(): void
    {
        $this->moveViewer(-1);
    }

    public function nextImage(): void
    {
        $this->moveViewer(1);
    }

    protected function moveViewer(int $direction): void
    {
        $ids = $this->imageQuery()->pluck('id')->map(fn($id) => (int)$id)->values();
        if ($ids->isEmpty()) {
            $this->closeViewer();
            return;
        }

        $current = $ids->search($this->viewerAttachmentId, true);
        $current = $current === false ? 0 : $current;
        $next = ($current + $direction + $ids->count()) % $ids->count();
        $this->viewerAttachmentId = $ids[$next];
    }

    protected function attachmentQuery()
    {
        $definition = $this->definition();

        return Attachment::query()
            ->where('table', $definition['table'])
            ->where('table_id', $this->contextId)
            ->orderBy('id');
    }

    protected function imageQuery()
    {
        return $this->attachmentQuery()->where('type', 'image');
    }

    protected function attachmentPath(Attachment $attachment): string
    {
        return FileBank::normalizePath(trim($attachment->directory, '/') . '/' . $attachment->attachment);
    }

    protected function proxyUrl(Attachment $attachment): string
    {
        $segments = array_map('rawurlencode', explode('/', $this->attachmentPath($attachment)));
        return '/filebank/' . implode('/', $segments);
    }

    protected function record(): ?Model
    {
        $model = $this->definition()['model'];
        return $model::find($this->contextId);
    }

    protected function directory(Model $record): string
    {
        return 'site/' . $record->site_id . '/' . $this->definition()['directory'];
    }

    protected function canView(?Model $record = null): bool
    {
        $record ??= $this->record();
        if (!$record || !Auth::check())
            return false;

        $definition = $this->definition();

        return ($definition['view_type'] ?? 'allowed2') === 'hasPermission2'
            ? Auth::user()->hasPermission2($definition['view'])
            : Auth::user()->allowed2($definition['view'], $record);
    }

    protected function canUpload(): bool
    {
        $record = $this->record();
        if (!$record || !Auth::check())
            return false;

        if ($this->context === 'site-maintenance') {
            return Auth::user()->allowed2('add.site.maintenance')
                || Auth::user()->allowed2('edit.site.maintenance', $record);
        }

        return Auth::user()->allowed2($this->definition()['upload'], $record);
    }

    protected function canDelete(): bool
    {
        $record = $this->record();
        return $record && Auth::check() && Auth::user()->allowed2($this->definition()['delete'], $record);
    }

    protected function definition(): ?array
    {
        return match ($this->context) {
            'site-foc' => [
                'model' => SiteFoc::class,
                'table' => 'site_foc',
                'directory' => 'foc',
                'view' => 'view.site.foc',
                'upload' => 'edit.site.foc',
                'delete' => 'del.site.foc',
            ],
            'site-prac-completion' => [
                'model' => SitePracCompletion::class,
                'table' => 'site_prac_completion',
                'directory' => 'prac',
                'view' => 'view.prac.completion',
                'upload' => 'edit.prac.completion',
                'delete' => 'del.prac.completion',
            ],
            'site-maintenance' => [
                'model' => SiteMaintenance::class,
                'table' => 'site_maintenance',
                'directory' => 'maintenance',
                'view' => 'view.site.maintenance',
                'upload' => 'edit.site.maintenance',
                'delete' => 'del.site.maintenance',
            ],
            'site-note' => [
                'model' => SiteNote::class,
                'table' => 'site_notes',
                'directory' => 'note',
                'view' => 'view.site.note',
                'view_type' => 'hasPermission2',
                'upload' => 'edit.site.note',
                'delete' => 'del.site.note',
            ],
            'site-inspection-electrical' => [
                'model' => SiteInspectionElectrical::class,
                'table' => 'site_inspection_electrical',
                'directory' => 'inspection',
                'view' => 'view.site.inspection',
                'upload' => 'edit.site.inspection',
                'delete' => 'del.site.inspection',
            ],
            'site-inspection-plumbing' => [
                'model' => SiteInspectionPlumbing::class,
                'table' => 'site_inspection_plumbing',
                'directory' => 'inspection',
                'view' => 'view.site.inspection',
                'upload' => 'edit.site.inspection',
                'delete' => 'del.site.inspection',
            ],
            'site-scaffold-handover' => [
                'model' => SiteScaffoldHandover::class,
                'table' => 'site_scaffold_handover',
                'directory' => 'scaffold',
                'view' => 'view.site.scaffold.handover',
                'upload' => 'edit.site.scaffold.handover',
                'delete' => 'del.site.scaffold.handover',
            ],
            default => null,
        };
    }

    public function render()
    {
        abort_unless($this->canView(), 403);

        $attachments = $this->attachmentQuery()->get();
        $images = $attachments->where('type', 'image')->values();
        $files = $attachments->where('type', '!=', 'image')->values();
        $viewerImage = $this->showViewer && $this->viewerAttachmentId
            ? $images->firstWhere('id', $this->viewerAttachmentId)
            : null;
        $attachmentUrls = $attachments->mapWithKeys(fn(Attachment $attachment) => [
            $attachment->id => $attachment->directory && $attachment->attachment ? $this->proxyUrl($attachment) : null,
        ]);
        $attachmentAvailable = $attachments->mapWithKeys(function (Attachment $attachment) {
            if (!$attachment->directory || !$attachment->attachment)
                return [$attachment->id => false];

            try {
                return [$attachment->id => FileBank::exists($this->attachmentPath($attachment))];
            } catch (\Throwable $e) {
                report($e);
                return [$attachment->id => null];
            }
        });

        return view('livewire.misc.attachments', [
            'attachments' => $attachments,
            'images' => $images,
            'files' => $files,
            'viewerImage' => $viewerImage,
            'attachmentUrls' => $attachmentUrls,
            'attachmentAvailable' => $attachmentAvailable,
            'canUpload' => $this->canUpload(),
            'canDelete' => $this->canDelete(),
        ]);
    }
}
