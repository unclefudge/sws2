<?php

namespace App\Models\Site;


use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SiteCdc extends Model
{

    protected $table = 'site_cdc';

    protected $fillable = [
        'site_id', 'super_id', 'supervisor_sign_by', 'supervisor_sign_at', 'manager_sign_by', 'manager_sign_at',
        'certifier', 'certifier_phone', 'pca_name', 'pca_email', 'inspections_email',
        'stage', 'cdc_received', 'cdc_requested', 'final_photos_rcvd', 'portal_fee_paid', 'wbo_waiting', 'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['cdc_received' => 'date', 'supervisor_sign_at' => 'datetime', 'manager_sign_at' => 'datetime', 'cdc_requested' => 'datetime', 'final_photos_rcvd' => 'date', 'portal_fee_paid' => 'boolean', 'wbo_waiting' => 'boolean',];


    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }


    public function supervisor()
    {
        return $this->belongsTo('App\User', 'super_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Site\SiteCdcItem', 'cdc_id');
    }


    /**
     * A Site CDC 'may' have been assigned to a Company.
     */
    public function assignedTo()
    {
        $assigned = SiteCdcItem::where('cdc_id', $this->id)->pluck('assigned_to')->toArray();
        return array_unique($assigned);

    }

    public function assignedToNames()
    {
        $names = '';
        foreach ($this->assignedTo() as $cid) {
            $company = Company::find($cid);
            if ($company)
                $names .= $company->name . ", ";
        }
        return rtrim($names, ', ');

    }


    /**
     * Determine if a all items are completed
     */
    public function itemsCompleted()
    {
        $completed = $this->items->filter(function ($item) {
            return $item->status == 0;
        });

        return $completed;
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_id')->where('table', 'site_cdc');
    }


    public function actions()
    {
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }


    public function todos($status = '')
    {
        return collect();
    }


    public function signedSupervisor()
    {
        return $this->belongsTo('App\User', 'supervisor_sign_by');
    }

    public function signedManager()
    {
        return $this->belongsTo('App\User', 'manager_sign_by');
    }

    /**
     * Determine if a report has been signed by 1 or 2
     *
     * @return integer
     */
    public function isSigned()
    {
        $count = 0;
        if ($this->supervisor_sign_by)
            $count++;
        if ($this->manager_sign_by)
            $count++;

        return $count;
    }

    public static function stageOptions(): array
    {
        return ['Upcoming' => 'Upcoming', 'CDC Requested' => 'CDC Requested', 'CDC Received' => 'CDC Received'];
    }

    public function calculateStage(): string
    {
        if ((int) $this->status === -1 || (int) $this->site?->status === -2) return 'Disabled';
        if ($this->site?->construction_rcvd !== null) return 'CDC Received';
        if ($this->cdc_requested !== null) return 'CDC Requested';
        return 'Upcoming';
    }

    /** Apply the register filters to the existing m (CDC) / s (Site) join. */
    public static function applyRegisterFilters(\Illuminate\Database\Query\Builder $query, string $stage, string $draftsperson = 'all'): void
    {
        if ($draftsperson === '__unassigned__') {
            $query->where(function ($query) {
                $query->whereNull('s.draftsperson_cc')->orWhereRaw('TRIM(s.draftsperson_cc) = ?', ['']);
            });
        } elseif ($draftsperson !== 'all') {
            $query->where('s.draftsperson_cc', $draftsperson);
        }

        if ($stage === 'Disabled') {
            $query->where(function ($query) {
                $query->where('m.status', -1)->orWhere('s.status', -2);
            });
            return;
        }
        $query->where('m.status', '!=', -1)->where('s.status', '!=', -2);
        if ($stage === 'CDC Received') {
            $query->whereNotNull('s.construction_rcvd');
        } elseif ($stage === 'CDC Requested') {
            $query->whereNotNull('m.cdc_requested')->whereNull('s.construction_rcvd');
        } elseif ($stage === 'Upcoming') {
            $query->whereNull('m.cdc_requested')->whereNull('s.construction_rcvd');
        }
    }

    public function syncStage(): bool
    {
        // The Site's Zoho-owned Construction Certificate date is authoritative.
        $received = $this->site?->construction_rcvd;
        $this->cdc_received = $received ? \Carbon\Carbon::parse($received)->toDateString() : null;
        $this->stage = $this->calculateStage();
        if (!$this->isDirty(['stage', 'cdc_received'])) return false;
        return $this->saveQuietly();
    }

    public static function ensureForSite(Site $site): ?self
    {
        // The new register is currently Cape Cod only. Do not backfill cancelled sites.
        if ((int) $site->company_id !== 3) return null;
        return \Illuminate\Support\Facades\DB::transaction(function () use ($site) {
            // Serialize creation from concurrent Zoho requests/CLI runs without a unique-index migration.
            Site::whereKey($site->id)->lockForUpdate()->firstOrFail();
            $cdc = static::where('site_id', $site->id)->first();
            if (!$cdc) {
                if ((int) $site->status === -2 || strtoupper(trim((string) $site->lodgement_type)) !== 'CDC') return null;
                $cdc = static::create(['site_id' => $site->id, 'status' => 1, 'stage' => 'Upcoming',
                    'created_by' => Auth::id() ?: 1, 'updated_by' => Auth::id() ?: 1]);
                Action::create(['action' => 'CDC created automatically', 'table' => 'site_cdc', 'table_id' => $cdc->id,
                    'created_by' => Auth::id() ?: 1, 'updated_by' => Auth::id() ?: 1]);
            }
            $cdc->setRelation('site', $site);
            $cdc->syncStage();
            return $cdc;
        });
    }

    public static function reconcileStages(): int
    {
        $updated = 0;
        static::with('site')->chunkById(200, function ($records) use (&$updated) {
            foreach ($records as $cdc) if ($cdc->syncStage()) $updated++;
        });
        return $updated;
    }

    /**
     * Move to On Hold by given user
     */
    public function moveToHold($user)
    {
        $action = Action::create(['action' => 'Moved CDC to On Hold', 'table' => $this->table, 'table_id' => $this->id]);
        $this->status = 4;
        $this->save();

        // Close current ToDoo for QA
        $this->closeToDo($user);
    }

    /**
     * Close any outstanding ToDoo
     */
    public function closeToDo($type = 'cdc')
    {
        // Disabled while CDC Requirements is being trialled.
    }

    /**
     * Move to Active by given user
     */
    public function moveToActive($user)
    {
        $action = Action::create(['action' => 'Moved CDC to Active', 'table' => $this->table, 'table_id' => $this->id]);
        $this->status = 1;
        $this->save();

        // Create ToDoo for QA
        $site = Site::findOrFail($this->site_id);
        // Trial mode: no tasks are created.
    }


    public function createAssignSupervisorToDo($user_list)
    {
        // Disabled while CDC Requirements is being trialled.
    }


    public function createManagerSignOffToDo($user_list)
    {
        // Disabled while CDC Requirements is being trialled.
    }

    /**
     * Create ToDoo for Report and assign to given user(s)
     */
    public function createSupervisorAssignedToDo($user_list)
    {
        // Disabled while CDC Requirements is being trialled.
    }

    /**
     * Email Assigned
     */
    public function emailAssigned($user)
    {
        // Disabled while CDC Requirements is being trialled.
    }

    /**
     * Email Action Notification
     */
    public function emailAction($action, $important = false)
    {
        // Disabled while CDC Requirements is being trialled.
    }

    /**
     * Last updated (record or action)
     *
     * @return Carbon date
     */
    public function lastUpdated()
    {
        $lastAction = $this->actions->sortBy('updated_at')->last();

        if ($lastAction && $lastAction->updated_at->gt($this->updated_at))
            return $lastAction->updated_at;
        else
            return $this->updated_at;
    }

    /**
     * Last Action
     *
     * @return instance of Action
     */
    public function lastAction()
    {
        return $this->actions->sortBy('updated_at')->last();
    }

    /**
     * Last Action Note
     *
     * @return string
     */
    public function lastActionNote()
    {
        $lastAction = $this->actions->sortBy('updated_at')->last();

        return ($lastAction) ? $lastAction->action : '';

    }

    /**
     * Display records last update_by + date
     *
     * @return string
     */
    public function displayUpdatedBy()
    {
        $user = User::findOrFail($this->updated_by);

        return '<span style="font-weight: 400">Last modified: </span>' . $this->updated_at->diffForHumans() . ' &nbsp; ' .
            '<span style="font-weight: 400">By:</span> ' . $user->fullname;
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
