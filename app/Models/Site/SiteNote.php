<?php

namespace App\Models\Site;

use App\Models\Misc\Attachment;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;


class SiteNote extends Model
{

    protected $table = 'site_notes';
    protected $fillable = ['site_id', 'category_id', 'price', 'variation_name', 'variation_info', 'variation_net', 'variation_cost', 'variation_days', 'response_req',
        'costing_location', 'costing_room', 'costing_item', 'costing_extra_credit', 'costing_priority', 'prac_notified', 'prac_meeting',
        'occupation_date', 'occupation_area', 'parent', 'status', 'notes', 'created_by', 'updated_by'];
    protected $casts = ['prac_notified' => 'datetime', 'prac_meeting' => 'datetime', 'occupation_date' => 'datetime'];


    /**
     * A SiteNote belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SiteNote belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Misc\Category');
    }

    /**
     * SiteNote Attachments
     */
    public function attachments()
    {
        return Attachment::where('table', $this->table)->where('table_id', $this->id)->get();
    }

    public function costs()
    {
        return $this->hasMany('App\Models\Site\SiteNoteCost', 'note_id');
    }

    // Extra Notes
    public function extraNotes()
    {
        return $this->hasMany('App\Models\Site\SiteNote', 'parent');
    }

    public function parentNote()
    {
        return $this->belongsTo('App\Models\Site\SiteNote', 'parent');
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


        if (\App::environment('prod')) {
            $email_to = [];
            if ($this->category->notify_users) {
                $users = explode(',', $this->category->notify_users);
                foreach ($users as $user_id) {
                    $user = User::find($user_id);
                    if ($user && validEmail($user->email))
                        $email_to[] = $user->email;
                }
            }
            // Include Site Supervisor on email
            if ($this->site->supervisor_id && validEmail($this->site->supervisor->email))
                $email_to[] = $this->site->supervisor->email;
        }

        if ($email_to)
            Mail::to($email_to)->cc('sitenote@safeworksite.com.au')->send(new \App\Mail\Site\SiteNoteCreated($this));

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