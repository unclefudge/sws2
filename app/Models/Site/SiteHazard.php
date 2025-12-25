<?php

namespace App\Models\Site;

use App\Http\Utilities\FailureTypes;
use App\Models\Comms\Todo;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Services\FileBank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_id')->where('table', 'site_hazards');
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

    public function saveCopyAttachment(string $filePath): void
    {
        if (!Storage::disk('local')->exists($filePath))
            return;

        $basePath = "site/{$this->site_id}/hazard";
        $originalName = basename($filePath);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $type = in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp']) ? 'image' : 'file';

        $filename = "hazard-{$this->id}-{$originalName}";
        $path = "{$basePath}/{$filename}";
        $counter = 1;

        while (FileBank::exists($path)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = "{$name}-{$counter}.{$extension}";
            $path = "{$basePath}/{$filename}";
            $counter++;
        }

        FileBank::put($path, new File(Storage::disk('local')->path($filePath)));
        Attachment::create(['table' => 'site_hazards', 'table_id' => $this->id, 'name' => $originalName, 'attachment' => $filename, 'directory' => "site/{$this->site_id}/hazard"]);
    }

    /**
     * Email Hazard
     */
    public function emailHazard($action)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (app()->environment('prod')) {
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

        if (app()->environment('prod')) {
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
    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment)
            return '';

        return FileBank::url("site/{$this->site_id}/hazard/{$this->attachment}");
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