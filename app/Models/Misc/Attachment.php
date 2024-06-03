<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class Attachment extends Model
{

    protected $table = 'attachments';
    protected $fillable = ['table', 'table_id', 'type', 'category', 'name', 'attachment', 'directory', 'order',
        'status', 'notes', 'created_by', 'created_at', 'updated_at', 'updated_by'];
    protected $casts = ['updated_at' => 'datetime'];

    /**
     * A Attachment belongs to a Parent Record
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function record()
    {
        if ($this->table == 'site_notes')
            return $this->belongsTo('App\Models\Site\SiteNotes', 'table_id');
        if ($this->table == 'site_hazards')
            return $this->belongsTo('App\Models\Site\SiteHazards', 'table_id');
        if ($this->table == 'site_qa')
            return $this->belongsTo('App\Models\Site\SiteQa', 'table_id');
        if ($this->table == 'site_asbestos')
            return $this->belongsTo('App\Models\Site\SiteAsbestos', 'table_id');
        if ($this->table == 'site_maintenance')
            return $this->belongsTo('App\Models\Site\SiteMaintenance', 'table_id');
        if ($this->table == 'site_prac_completion')
            return $this->belongsTo('App\Models\Site\SitePracCompletion', 'table_id');
        if ($this->table == 'site_inspection_plumbing')
            return $this->belongsTo('App\Models\Site\SiteInspectionPlumbing', 'table_id');
        if ($this->table == 'site_inspection_electrical')
            return $this->belongsTo('App\Models\Site\SiteInspectionElectrical', 'table_id');
        if ($this->table == 'company_docs_review')
            return $this->belongsTo('App\Models\Company\CompanyDoc', 'table_id');
        if ($this->table == 'companys')
            return $this->belongsTo('App\Models\Company\Company', 'table_id');

    }

    /**
     * Save Attachment
     */
    public function saveAttachment($tmp_filename, $filename_prefix = '', $name = '')
    {
        $tempFile = TemporaryFile::where('folder', $tmp_filename)->first();
        if ($tempFile) {
            // Move temp file to support ticket directory
            $dir = $this->directory;
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

            $tempFilePublicPath = public_path($tempFile->folder) . "/" . $tempFile->filename;
            if (file_exists($tempFilePublicPath)) {
                $newFile = ($filename_prefix) ? $filename_prefix . $tempFile->filename : $tempFile->filename;

                // Ensure filename is unique by adding counter to similar filenames
                $count = 1;
                while (file_exists(public_path("$dir/$newFile"))) {
                    $ext = pathinfo($newFile, PATHINFO_EXTENSION);
                    $filename = pathinfo($newFile, PATHINFO_FILENAME);
                    $newFile = $filename . $count++ . ".$ext";
                }
                rename($tempFilePublicPath, public_path("$dir/$newFile"));

                // Determine file extension and set type
                $ext = pathinfo($tempFile->filename, PATHINFO_EXTENSION);

                // Update record

                $this->name = ($name) ? $name : pathinfo($tempFile->filename, PATHINFO_BASENAME);
                $this->type = (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'])) ? 'image' : 'file';
                $this->attachment = $newFile;
                //$this->file = public_path("$dir/$newFile");
                $this->save();
            }

            // Delete Temporary file directory + record
            $tempFile->delete();
            $files = scandir($tempFile->folder);
            if (count($files) == 0)
                rmdir(public_path($tempFile->folder));
        }
    }


    /**
     * A Attachment belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }


    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->record->owned_by;
    }

    /**
     * Get the Attachment URL (setter)
     */
    public function getUrlAttribute()
    {
        if ($this->attributes['directory'] && $this->attributes['attachment'])
            return $this->attributes['directory'] . '/' . $this->attributes['attachment'];

        return '';
    }

    /**
     * The "booting" method of the model.
     *
     * Overrides parent function
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        if (Auth::check()) {
            // create a event to happen on creating
            static::creating(function ($table) {
                $table->created_by = Auth::user()->id;
                $table->updated_by = Auth::user()->id;
            });

            // create a event to happen on updating
            static::updating(function ($table) {
                $table->updated_by = Auth::user()->id;
            });
        }
    }
}