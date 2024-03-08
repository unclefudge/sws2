<?php

namespace App\Models\Site;

use App\Http\Utilities\FailureTypes;
use App\Models\Comms\Todo;
use App\Models\Misc\Action;
use App\Models\Misc\TemporaryFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class SiteHazard extends Model
{

    protected $table = 'site_hazards';
    protected $fillable = [
        'site_id', 'reason', 'location', 'source', 'rating', 'failure', 'action_required',
        'attachment', 'notes', 'status', 'resolved_at',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];
    protected $casts = ['resolved_at' => 'datetime'];

    /**
     * A SiteHazard belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SiteHazard has many SiteHazardFiles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany('App\Models\Site\SiteHazardFile', 'hazard_id');
    }

    /**
     * A SiteHazard belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A SiteHazards has many Actions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }

    /**
     * A SiteHazard 'may' have multiple ToDoos
     *
     * @return Collection
     */
    public function todos($status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'hazard')->where('type_id', $this->id)->get();

        return Todo::where('type', 'hazard')->where('type_id', $this->id)->get();
    }

    /**
     * Update Status
     */
    public function updateStatus($status)
    {
        $old = $this->status;
        $this->status = $status;
        $this->save();

        if ($old != $status) {
            $mesg = ($status) ? 'Hazard has been re-opened' : 'Hazard has been closed';
            $action = Action::create(['action' => $mesg, 'table' => $this->table, 'table_id' => $this->id]);
            $this->emailAction($action, 'important');
        }
    }


    /**
     * Save attached Media to existing Issue
     */
    public function saveAttachment($tmp_filename)
    {
        $tempFile = TemporaryFile::where('folder', $tmp_filename)->first();
        if ($tempFile) {
            // Move temp file to hazard directory
            $dir = "filebank/site/" . $this->site->id . '/hazard';
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

            $tempFilePublicPath = public_path($tempFile->folder) . "/" . $tempFile->filename;
            if (file_exists($tempFilePublicPath)) {
                $newFile = "hazard-" . $this->id . '-' . $tempFile->filename;

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
                $orig_filename = pathinfo($tempFile->filename, PATHINFO_BASENAME);
                $type = (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'])) ? 'image' : 'file';
                $new = SiteHazardFile::create(['hazard_id' => $this->id, 'type' => $type, 'name' => $orig_filename, 'attachment' => $newFile]);
            }

            // Delete Temporary file directory + record
            $tempFile->delete();
            $files = scandir($tempFile->folder);
            if (count($files) == 0)
                rmdir(public_path($tempFile->folder));
        }
    }

    public function saveCopyAttachment($file)
    {
        if ($file) {
            // Copy file to hazard directory
            $dir = "filebank/site/" . $this->site->id . '/hazard';
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

            if (file_exists(public_path($file))) {
                $newFile = "hazard-" . $this->id . '-' . pathinfo($file, PATHINFO_FILENAME);

                // Ensure filename is unique by adding counter to similar filenames
                $count = 1;
                while (file_exists(public_path("$dir/$newFile"))) {
                    $ext = pathinfo($newFile, PATHINFO_EXTENSION);
                    $filename = pathinfo($newFile, PATHINFO_FILENAME);
                    $newFile = $filename . $count++ . ".$ext";
                }
                copy(public_path($file), public_path("$dir/$newFile"));

                // Determine file extension and set type
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $filename = pathinfo($newFile, PATHINFO_FILENAME);
                $type = (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'])) ? 'image' : 'file';
                $new = SiteHazardFile::create(['hazard_id' => $this->id, 'type' => $type, 'name' => $filename, 'attachment' => $newFile]);
            }
        }
    }

    /**
     * Email Hazard
     */
    public function emailHazard($action)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = $this->site->company->notificationsUsersEmailType('site.hazard');
           
            // Add supervisor email
            if ($this->site->supervisorEmail && !in_array($this->site->supervisorEmail, $email_to))
                $email_to[] = $this->site->supervisorEmail;
            // Georgie (458) notify to site 0003-vehicles (809)
            if ($this->site->id == '809')
                $email_to[] = "georgie@capecod.com.au";

            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteHazardCreated($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteHazardCreated($this, $action));

    }

    /**
     * Email Action Notification
     */
    public function emailAction($action, $important = false)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = $this->site->company->notificationsUsersEmailType('site.hazard');
            if ($this->site->supervisorEmail && !in_array($this->site->supervisorEmail, $email_to))
                $email_to[] = $this->site->supervisorEmail;
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteHazardAction($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteHazardAction($this, $action));
    }

    /**
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'])
            return '/filebank/site/' . $this->attributes['site_id'] . "/hazard/" . $this->attributes['attachment'];

        return '';
    }

    /**
     * Get the Failure Type (setter)
     */
    public function getFailureTypeAttribute()
    {
        return ($this->attributes['failure'] == 0) ? '' : FailureTypes::name($this->attributes['failure']);
    }

    /**
     * Get the Risk Rating Text (setter)
     */
    public function getRatingTextAttribute()
    {
        if ($this->attributes['rating'] == '1')
            return 'Low';
        if ($this->attributes['rating'] == '2')
            return 'Medium';
        if ($this->attributes['rating'] == '3')
            return 'High';
        if ($this->attributes['rating'] == '4')
            return 'Extreme';
    }

    /**
     * Get the Risk Rating Text (setter)
     */
    public function getRatingTextColouredAttribute()
    {
        if ($this->attributes['rating'] == '1')
            return '<span style="background:#00cc99; color:#fff; padding:5px 10px">Low</span>';
        if ($this->attributes['rating'] == '2')
            return '<span style="background:#ffcc66; color:#fff; padding:5px 10px">Medium</span>';
        if ($this->attributes['rating'] == '3')
            return '<span style="background:#ff9900; color:#fff; padding:5px 10px">High</span>';
        if ($this->attributes['rating'] == '4')
            return '<span style="background:#ff0000; color:#fff; padding:5px 10px">Extreme</span>';
    }


    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->site->company;
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