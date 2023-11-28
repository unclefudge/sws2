<?php

namespace App\Models\Site;

use PDF;
use URL;
use Mail;
use App\User;
use App\Models\Comms\Todo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SiteNote extends Model {

    protected $table = 'site_notes';
    protected $fillable = ['site_id', 'category_id', 'price', 'attachment', 'status', 'notes'];

    //protected $dates = [''];

    /**
     * A SiteNotes belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SiteNotes belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Misc\Category');
    }

    /**
     * A SiteNote belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * Email Hazard
     */
    public function emailNote($email_list = '')
    {
        $email_to = [env('EMAIL_DEV')];

        //if (\App::environment('prod')) {
            $email_to = [];
            if ($this->category->notify_users) {
                $users = explode(',', $this->category->notify_users);
                foreach ($users as $user_id) {
                    $user = User::find($user_id);
                    if ($user && validEmail($user->email))
                        $email_to[] = $user->email;
                }
            }
        //}

       if ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteNoteCreated($this));

    }

    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getCategoryNameAttribute()
    {
        return $this->category->name;
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
            // create an event to happen on creating
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