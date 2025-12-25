<?php

namespace App\Models\Misc;

use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\SiteAsbestos;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteNote;
use App\Models\Site\SitePracCompletion;
use App\Models\Site\SiteQa;
use App\Services\FileBank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mail;
use URL;

class Attachment extends Model
{

    protected $table = 'attachments';
    protected $fillable = ['table', 'table_id', 'type', 'category', 'name', 'attachment', 'directory', 'order',
        'status', 'notes', 'created_by', 'created_at', 'updated_at', 'updated_by'];
    protected $casts = ['updated_at' => 'datetime'];

    /* ============================================================
     |  Relationships
     |============================================================ */
    public function record()
    {
        return match ($this->table) {
            'companys' => $this->belongsTo(Company::class, 'table_id'),
            'company_docs_review' => $this->belongsTo(CompanyDoc::class, 'table_id'),
            'site_asbestos' => $this->belongsTo(SiteAsbestos::class, 'table_id'),
            'site_incidents' => $this->belongsTo(SiteIncident::class, 'table_id'),
            'site_inspection_plumbing' => $this->belongsTo(SiteInspectionPlumbing::class, 'table_id'),
            'site_inspection_electrical' => $this->belongsTo(SiteInspectionElectrical::class, 'table_id'),
            'site_hazards' => $this->belongsTo(SiteHazard::class, 'table_id'),
            'site_maintenance' => $this->belongsTo(SiteMaintenance::class, 'table_id'),
            'site_prac_completion' => $this->belongsTo(SitePracCompletion::class, 'table_id'),
            'site_notes' => $this->belongsTo(SiteNote::class, 'table_id'),
            'site_qa' => $this->belongsTo(SiteQa::class, 'table_id'),
            default => null,
        };
    }

    /* ============================================================
      | FilePond / TemporaryFile uploads (TEMP → FILEBANK)
      |============================================================ */
    public function saveAttachment(string $tmpFolder, string $filenamePrefix = '', string $name = ''): void
    {
        $tempFile = TemporaryFile::where('folder', $tmpFolder)->first();
        if (!$tempFile) return;

        $sourcePath = "{$tempFile->folder}/{$tempFile->filename}";

        // Temp file must exist
        if (!Storage::disk('local')->exists($sourcePath)) {
            $tempFile->delete();
            return;
        }

        // Logical base path (NO leading slash, NO public/)
        $basePath = trim($this->directory, '/');

        // Determine attachment type
        $extension = strtolower(pathinfo($tempFile->filename, PATHINFO_EXTENSION));
        $this->type = in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp']) ? 'image' : 'file';

        // Optional filename prefix
        $forcedFilename = $filenamePrefix ? $filenamePrefix . $tempFile->filename : null;

        // Wrap local temp file as UploadedFile-compatible object
        $localFile = new File(Storage::disk('local')->path($sourcePath));

        // Store via FileBank (Spaces-safe, unique, streamed)
        $filename = FileBank::storeUploadedFile($localFile, $basePath, $forcedFilename, $this->type === 'image');

        // Persist DB record
        $this->name = $name ?: $tempFile->filename;
        $this->attachment = $filename;
        $this->save();

        // Cleanup temp storage
        Storage::disk('local')->deleteDirectory($tempFile->folder);
        $tempFile->delete();
    }


    /* ============================================================
     | Delete attachment in Spaces
     |============================================================ */
    public function deleteAttachment(): void
    {
        if ($this->directory && $this->attachment) {
            $path = trim($this->directory, '/') . '/' . $this->attachment;
            FileBank::delete($path);
        }

        $this->delete();
    }

    /* ============================================================
    | Mailgun / filesystem uploads
    |============================================================ */
    public function moveAttachment(string $localFilePath, string $prefix = '', string $name = ''): void
    {
        if (!file_exists($localFilePath))
            return;

        $originalName = basename($localFilePath);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $this->type = in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp']) ? 'image' : 'file';

        $filename = $this->uniqueFilename($prefix . $originalName);
        $path = "{$this->directory}/{$filename}";

        // STREAM → Spaces
        $stream = fopen($localFilePath, 'rb');
        Storage::disk('filebank_spaces')->put($path, $stream);
        fclose($stream);

        unlink($localFilePath);

        $this->name = $name ?: $originalName;
        $this->attachment = $filename;
        $this->save();
    }

    /* ============================================================
     | Helpers
     |============================================================ */
    protected function uniqueFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $counter = 1;

        $path = "{$this->directory}/{$filename}";

        while (FileBank::exists($path)) {
            $filename = "{$name}-{$counter}.{$ext}";
            $path = "{$this->directory}/{$filename}";
            $counter++;
        }

        return $filename;
    }

    /* ============================================================
     |  Accessors
     |============================================================ */

    public function getUrlAttribute(): string
    {
        if (!$this->directory || !$this->attachment)
            return '';

        return FileBank::url(trim($this->directory, '/') . '/' . $this->attachment);
    }

    /* ============================================================
     |  Ownership
     |============================================================ */

    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function getOwnedByAttribute()
    {
        return $this->record?->owned_by;
    }

    /* ============================================================
     |  Model Events
     |============================================================ */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if ($model->directory && $model->attachment) {
                $path = trim($model->directory, '/') . '/' . $model->attachment;
                FileBank::delete($path);
            }
        });
    }
}