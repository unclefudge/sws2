<?php

namespace App\Models\Site;

use App\Models\Comms\Todo;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;


class SiteShutdown extends Model
{

    protected $table = 'site_shutdown';
    protected $fillable = [
        'site_id', 'super_id', 'version', 'attachment',
        'supervisor_sign_by', 'supervisor_sign_at', 'manager_sign_by', 'manager_sign_at', 'notes', 'status',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];
    protected $casts = ['supervisor_sign_at' => 'datetime', 'manager_sign_at' => 'datetime', 'approved_at' => 'datetime'];


    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    public function supervisor()
    {
        return $this->belongsTo('App\User', 'super_id');
    }


    public function items()
    {
        return $this->hasMany('App\Models\Site\SiteShutdownItem', 'shutdown_id');
    }


    /*
    * List of items completed
    */
    public function itemsCompleted()
    {
        $completed = [];
        foreach ($this->items as $item) {
            if ($item->response)
                $completed[] = $item->id;
        }
        return SiteShutdownItem::find($completed);
    }


    /**
     * Create ToDoo for ProjectSupply and assign to given user(s)
     */
    public function createSupervisorToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type' => 'site shutdown',
            'type_id' => $this->id,
            'name' => 'Site Shutdown - ' . $site->name,
            'info' => 'Please update the site shutdown for this site.',
            'due_at' => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->site->company_id,
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Create ToDoo for ProjectSupply and assign to given user(s)
     */
    public function createSignOffToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type' => 'site shutdown',
            'type_id' => $this->id,
            'name' => 'Site Shutdown - ' . $site->name,
            'info' => 'Please sign off on completed items',
            'due_at' => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->site->company_id,
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Close any outstanding ToDoo for this Project Supply
     */
    public function closeToDo()
    {
        $todos = Todo::where('type', 'site shutdown')->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = (Auth::check()) ? Auth::user()->id : 1;
            $todo->save();
        }
    }

    /**
     * Display records last update_by + date
     *
     * @return string
     */
    public function displayUpdatedBy()
    {
        $user = User::find($this->updated_by);

        return ($user) ? '<span style="font-weight: 400">Last modified: </span>' . $this->updated_at->diffForHumans() . ' &nbsp; ' .
            '<span style="font-weight: 400">By:</span> ' . $user->fullname : "$this->updated_by";
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
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'] && file_exists(public_path('/filebank/site/' . $this->site_id . '/docs/' . $this->attributes['attachment'])))
            return '/filebank/site/' . $this->site_id . '/docs/' . $this->attributes['attachment'];

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