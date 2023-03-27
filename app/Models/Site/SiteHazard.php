<?php

namespace App\Models\Site;

use URL;
use Mail;
use App\Models\Misc\Action;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Http\Utilities\FailureTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use nilsenj\Toastr\Facades\Toastr;

class SiteHazard extends Model {

    protected $table = 'site_hazards';
    protected $fillable = [
        'site_id', 'reason', 'location', 'source', 'rating', 'failure', 'action_required',
        'attachment', 'notes', 'status', 'resolved_at',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected $dates = ['resolved_at'];
    //public $timestamps = false;

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
            $mesg = ($status) ? 'Hazard has been re-opened' : 'Hazard has been resolved';
            $action = Action::create(['action' => $mesg, 'table' => $this->table, 'table_id' => $this->id]);
            $this->emailAction($action, 'important');
        }
    }


    /**
     * Save attached Media to existing Issue
     */
    public function saveAttachedMedia($file)
    {
        $site = Site::findOrFail($this->site_id);
        $path = "filebank/site/" . $site->id . '/hazard';
        $name = 'hazard-' . $site->code . '-' . $this->id . '-' . Auth::user()->id . '-1.' . strtolower($file->getClientOriginalExtension());

        // Ensure filename is unique by adding counter to similiar filenames
        $count = 2;
        while (file_exists(public_path("$path/$name")))
            $name = 'hazard-' . $site->code . '-' . $this->id . '-' . Auth::user()->id . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());

        $path_name = $path . '/' . $name;
        $file->move($path, $name);

        // resize the image to a width of 1024 and constrain aspect ratio (auto height)
        if (exif_imagetype($path_name)) {
            Image::make(url($path_name))
                ->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($path_name);
        }// else
        //    Toastr::error("Bad image");

        $this->attachment = $name;
        $this->save();
    }

    /**
     * Email Hazard
     */
    public function emailHazard($action)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_list = $this->site->company->notificationsUsersEmailType('site.hazard');
            $email_supers = $this->site->supervisorsEmails();
            $email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
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
            $email_list = $this->site->company->notificationsUsersEmailType('site.hazard');
            $email_supers = $this->site->supervisorsEmails();
            $email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
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