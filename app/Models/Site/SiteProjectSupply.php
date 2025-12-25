<?php

namespace App\Models\Site;

use App\Models\Comms\Todo;
use App\Models\Site\Planner\SitePlanner;
use App\Services\FileBank;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;


class SiteProjectSupply extends Model
{

    protected $table = 'project_supply';
    protected $fillable = [
        'site_id', 'version', 'approved_by', 'approved_at', 'attachment',
        'supervisor_sign_by', 'supervisor_sign_at', 'manager_sign_by', 'manager_sign_at', 'notes', 'status',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];
    protected $casts = ['supervisor_sign_at' => 'datetime', 'manager_sign_at' => 'datetime', 'approved_at' => 'datetime'];

    /**
     * A SiteProjectSupply belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }


    /**
     * A SiteProjectSupply has many Items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany('App\Models\Site\SiteProjectSupplyItem', 'supply_id');
    }

    /*
     * List of items ordered
     */
    public function itemsOrdered()
    {
        $ordered = [];
        $specials = 0;
        foreach ($this->items as $item) {
            if ($item->product_id > 1) {
                $product = SiteProjectSupplyProduct::find($item->product_id);
                $order = ($product->id == 2) ? "100" . $specials++ : $product->order;
                $ordered[$order] = $item;
            }
        }
        ksort($ordered);

        return $ordered;
    }

    /*
    * List of items completed
    */
    public function itemsCompleted()
    {
        $completed = [];
        foreach ($this->items as $item) {
            if ($item->supplier && $item->type && $item->colour)
                $completed[] = $item->id;
        }


        return SiteProjectSupplyItem::find($completed);
    }

    /*
    * Initialise Project Supply record  - add default items
    */
    public function initialise()
    {
        $maxID = SiteProjectSupplyProduct::all()->count();

        for ($i = 3; $i <= $maxID; $i++) {
            $product = SiteProjectSupplyProduct::findOrFail($i);
            $item = SiteProjectSupplyItem::create(['supply_id' => $this->id, 'product_id' => $product->id, 'product' => $product->name]);
        }

        return true;
    }

    public function reset()
    {
        foreach ($this->items as $item)
            $item->delete();

        $this->initialise();
        $this->supervisor_sign_at = '';
        $this->manager_sign_at = '';
        $this->approved_at = '';
        $this->save();

        return true;
    }

    /*
    * List of Product Titles
    */
    public function titles()
    {
        return SiteProjectSupplyProduct::find(1);
    }

    public function lockupCompleted()
    {
        $lockup = [32, 33, 3, 4, 5, 6, 7, 8, 9];
        $items = SiteProjectSupplyItem::where('supply_id', $this->id)->whereIn('product_id', $lockup)->get();
        foreach ($items as $item) {
            if (!($item->supplier && $item->type && $item->colour))
                return false;
        }
        return true;
    }

    /**
     * Create ToDoo for ProjectSupply and assign to given user(s)
     */
    public function createReviewToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type' => 'project supply',
            'type_id' => $this->id,
            'name' => 'Project Supply Information - ' . $site->name,
            'info' => 'Please update the supplied products for this site.',
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
            'type' => 'project supply',
            'type_id' => $this->id,
            'name' => 'Project Supply Information - ' . $site->name,
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
        $todos = Todo::where('type', 'project supply')->where('type_id', $this->id)->where('status', '1')->get();
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

    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment)
            return '';

        return FileBank::url("site/{$this->site_id}/docs/{$this->attachment}");
    }

    public function getLockupDateAttribute()
    {
        $plan = SitePlanner::where('site_id', $this->site_id)->where('task_id', 117)->first();
        if ($plan)
            return $plan->from->format('d/m/Y');
        return '';
    }

    public function getPracCompleteDateAttribute()
    {
        $plan = SitePlanner::where('site_id', $this->site_id)->where('task_id', 265)->first();
        if ($plan)
            return $plan->from->format('d/m/Y');
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