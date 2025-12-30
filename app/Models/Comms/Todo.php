<?php

namespace App\Models\Comms;

use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocPeriodTrade;
use App\Models\Company\CompanyDocReview;
use App\Models\Misc\Attachment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceItem;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteScaffoldHandover;
use App\Models\Site\SiteShutdown;
use App\Models\User\UserDoc;
use App\Services\FileBank;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class Todo extends Model
{

    protected $table = 'todo';
    protected $fillable = [
        'name', 'info', 'comments', 'type', 'type_id', 'type_id2', 'due_at', 'done_at', 'done_by',
        'priority', 'attachment', 'status', 'company_id', 'created_by', 'updated_by'
    ];
    protected $casts = ['due_at' => 'datetime', 'done_at' => 'datetime'];

    /**
     * A Todoo belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A Todoo belongs to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }

    /**
     * A Todoo is assigned to many Users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\Comms\TodoUser', 'todo_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_id')->where('table', 'todo');
    }

    /**
     * A Todoo is assigned to many Users
     *
     * @return collection of users
     */
    public function assignedTo()
    {
        $user_list = $this->users->pluck('user_id')->toArray();

        return User::whereIn('id', $user_list)->get();
    }

    /**
     * A Todoo is assigned to many users - return list separated by comma
     *
     * return string
     */
    public function assignedToBySBC()
    {
        $string = '';
        foreach ($this->assignedTo() as $user)
            $string .= $user->fullname . ', ';

        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * A Todoo is assigned to many users - return list separated by comma
     *
     * return string
     */
    public function assignedToCompanyBySBC()
    {
        $string = '';
        foreach ($this->assignedTo() as $user)
            $string .= $user->fullname . ' (' . $user->company->name . '), ';

        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * A Todoo MAY have a EquipmentLocation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function location()
    {
        return $this->hasOne('App\Models\Misc\Equipment\EquipmentLocation', 'id', 'type_id');
    }

    /**
     * A Todoo is done 'completed' by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function doneBy()
    {
        return $this->belongsTo('App\User', 'done_by');
    }


    public function record()
    {
        return match ($this->type) {
            'accident' => null,
            'company doc' => CompanyDoc::find($this->type_id),
            'company doc review' => CompanyDocReview::find($this->type_id),
            'equipment' => EquipmentLocation::find($this->type_id),
            'extension', 'extension signoff' => SiteExtension::find($this->type_id),
            'hazard' => SiteHazard::find($this->type_id),
            'incident', 'incident prevent', 'incident review' => SiteIncident::find($this->type_id),
            'inspection_electrical' => SiteInspectionElectrical::find($this->type_id),
            'inspection_plumbing' => SiteInspectionPlumbing::find($this->type_id),
            'maintenance' => SiteMaintenance::find($this->type_id),
            'maintenance_item' => optional(SiteMaintenanceItem::find($this->type_id))->maintenance,
            'project supply' => SiteProjectSupply::find($this->type_id),
            'qa' => SiteQa::find($this->type_id),
            'scaffold handover' => SiteScaffoldHandover::find($this->type_id),
            'site shutdown' => SiteShutdown::find($this->type_id),
            'supervisor' => null,
            'super checklist', 'super checklist signoff' => SuperChecklist::find($this->type_id),
            'swms' => WmsDoc::find($this->type_id),
            'toolbox' => ToolboxTalk::find($this->type_id),
            'user doc' => UserDoc::find($this->type_id),
            default => null,
        };
        /*
          'inspection' => 'Site Inspection',
          'company ptc' => 'Period Trade Contract',
          'company privacy' => 'Company Privacy Policy',
        */
    }

    /**
     * A Todoo is often linked to a webpage
     *
     * @return url
     */
    public function url(): string
    {
        return match ($this->type) {
            'asbestos notify' => "/site/asbestos/notification/{$this->type_id}/edit",
            'company doc' => $this->companyDocUrl(),
            'company doc review' => $this->companyDocReviewUrl(),
            'company ptc' => $this->companyPtcUrl(),
            'dial_before_dig' => "/site/doc",
            'extension', 'extension signoff' => "/site/extension",
            'foc' => "/site/foc/{$this->type_id}",
            'incident review' => "/site/incident/{$this->type_id}",
            'incident witness' => $this->incidentWitnessUrl(),
            'inspection_electrical' => "/site/inspection/electrical/{$this->type_id}",
            'inspection_plumbing' => "/site/inspection/plumbing/{$this->type_id}",
            'maintenance' => "/site/maintenance/{$this->type_id}",
            'maintenance_item' => $this->maintenanceItemUrl(),
            'prac_completion' => "/site/prac-completion/{$this->type_id}",
            'project supply' => "/site/supply/{$this->type_id}/edit",
            'qa' => "/site/qa/{$this->type_id}",
            'site shutdown' => "/site/shutdown/{$this->type_id}/edit",
            'super checklist' => "/supervisor/checklist/{$this->type_id}/{$this->type_id2}",
            'super checklist signoff' => "/supervisor/checklist/{$this->type_id}/weekly",
            'scaffold handover' => "/site/scaffold/handover/{$this->type_id}/edit",
            'toolbox' => "/safety/doc/toolbox2/{$this->type_id}",
            default => "/todo/{$this->id}",
        };
    }

    protected function maintenanceItemUrl(): string
    {
        $item = SiteMaintenanceItem::find($this->type_id);
        return $item && $item->maintenance ? "/site/maintenance/{$item->maintenance->id}" : "/todo/{$this->id}";
    }

    protected function incidentWitnessUrl(): string
    {
        $witness = SiteIncidentWitness::find($this->type_id);
        return $witness && $witness->incident ? "/site/incident/{$witness->incident->id}/witness/{$this->type_id}" : "/todo/{$this->id}";
    }

    protected function companyDocUrl(): string
    {
        $doc = CompanyDoc::find($this->type_id);
        if (!$doc) return "/todo/{$this->id}";
        return ($doc->expiry && $doc->expiry->gt(now())) ? "/company/{$doc->for_company_id}/doc/{$doc->id}/edit" : "/company/{$doc->for_company_id}/doc";
    }

    protected function companyDocReviewUrl(): string
    {
        $doc = CompanyDocReview::find($this->type_id);
        return $doc ? "/company/doc/standard/review/{$doc->id}/edit" : "/todo/{$this->id}";
    }

    protected function companyPtcUrl(): string
    {
        $ptc = CompanyDocPeriodTrade::find($this->type_id);
        return $ptc ? "/company/{$ptc->for_company_id}/doc/period-trade-contract/{$this->type_id}" : "/todo/{$this->id}";
    }

    /**
     * Assign a list of users to the ToDo
     */
    public function assignUsers($user_ids)
    {
        if (is_array($user_ids))
            foreach ($user_ids as $user_id) {
                TodoUser::create(['todo_id' => $this->id, 'user_id' => $user_id]); // Assign users
            }
        elseif ($user_ids)
            TodoUser::create(['todo_id' => $this->id, 'user_id' => $user_ids]); // Assign users

    }


    /**
     * A Notify 'may' have been opened by multiple users
     *
     * return collection
     */
    public function openedBy()
    {
        $user_ids = TodoUser::where('todo_id', $this->id)->where('opened', 1)->pluck('user_id')->toArray();

        return User::whereIn('id', $user_ids)->orderBy('firstname')->get();
    }

    /**
     * A Todoo is assigned to many users - return list separated by comma
     *
     * return string
     */
    public function openedBySBC()
    {
        $string = '';
        foreach ($this->assignedTo() as $user) {
            if ($this->isOpenedBy($user)) {
                $todo_user = TodoUser::where('todo_id', $this->id)->where('user_id', $user->id)->where('opened', 1)->first();
                $string .= $user->fullname . ' (' . $todo_user->opened_at->format('j/n/y') . '), ';
            } else
                $string .= $user->fullname . ', ';
        }
        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * Has a Todoo been opened by User (x)
     *
     * return booleen
     */
    public function isOpenedBy($user)
    {
        $record = TodoUser::where('todo_id', $this->id)->where('user_id', $user->id)->first();

        if ($record && $record->opened)
            return true;

        return false;
    }

    /**
     * Marked Todoo opened by User (x)
     */
    public function markOpenedBy($user)
    {
        $record = TodoUser::where('todo_id', $this->id)->where('user_id', $user->id)->first();

        if ($record && !$record->opened) {
            $record->opened = 1;
            $record->opened_at = Carbon::now();
            $record->save();
        }
    }

    /**
     * Close Todoo
     */
    public function close()
    {
        $this->status = 0;
        $this->done_at = Carbon::now();
        $this->done_by = (Auth::check()) ? Auth::user()->id : 1;
        $this->save();
    }


    /**
     * Email ToDoo
     */
    public function emailToDo($emailTo = '', $emailCc = '')
    {
        $isProd = app()->environment('prod');
        $isLocal = app()->environment(['local', 'dev']);

        //--------------------------------------------------------------------------
        // Resolve TO recipients
        //--------------------------------------------------------------------------
        if ($isProd) {
            // If not explicitly provided or marked as ASSIGNED, resolve assigned users
            if (!$emailTo || $emailTo === 'ASSIGNED') {
                $emailTo = [];

                foreach ($this->assignedTo() as $user) {
                    if (validEmail($user->email))
                        $emailTo[] = $user->email;
                }
            }
        } else
            $emailTo = [env('EMAIL_ME')];

        //--------------------------------------------------------------------------
        // Resolve CC recipients
        // --------------------------------------------------------------------------
        $cc = [];

        // Default CC: current user in production
        if ($isProd && Auth::check() && validEmail(Auth::user()->email))
            $cc[] = Auth::user()->email;

        // Exclude CC for specific ToDo types
        $excludeCcTypes = ['inspection_plumbing', 'inspection_electrical', 'toolbox', 'extension signoff', 'scaffold handover', 'maintenance',];

        if (in_array($this->type, $excludeCcTypes, true))
            $cc = [];

        // Exclude CC for Company Doc Approval requests
        if (preg_match('/^Company Document Approval Request/', $this->name))
            $cc = [];

        // Merge explicitly supplied CC addresses (prod only)
        if ($isProd && $emailCc)
            $cc = array_merge($cc, is_array($emailCc) ? $emailCc : [$emailCc]);

        //--------------------------------------------------------------------------
        // Send email
        //--------------------------------------------------------------------------
        if ($emailTo && $cc)
            Mail::to($emailTo)->cc($cc)->send(new \App\Mail\Comms\TodoCreated($this));
        elseif ($emailTo)
            Mail::to($emailTo)->send(new \App\Mail\Comms\TodoCreated($this));
    }

    /**
     * Email ToDoo
     */
    public function emailToDoCompleted($emailTo = null): void
    {
        // -----------------------------
        // Resolve TO recipients
        // -----------------------------
        $emailTo = [env('EMAIL_ME')];
        if (app()->environment('prod')) {
            if (!$emailTo) {
                $emailTo = collect($this->assignedTo())->pluck('email')->filter(fn($email) => validEmail($email))->values()->all();
            }

        }
        if (empty($emailTo)) return;

        // -----------------------------
        // Resolve CC (current user)
        // -----------------------------
        $cc = [];
        if (app()->environment('prod') && Auth::check() && validEmail(Auth::user()->email))
            $cc[] = Auth::user()->email;

        // -----------------------------
        // Send email
        // -----------------------------
        $mail = Mail::to($emailTo);
        if (!empty($cc)) $mail->cc($cc);
        $mail->send(new \App\Mail\Comms\TodoCompleted($this));
    }

    public function emailToDoReminder($emailTo = [])
    {
        // ------------------------------
        // Determine primary recipients
        // ------------------------------
        $emailTo = [env('EMAIL_ME')];
        if (app()->environment('prod')) {
            if (empty($emailTo))
                $emailTo = collect($this->assignedTo())->pluck('email')->filter(fn($email) => validEmail($email))->values()->all();
        }
        if (empty($emailTo)) return;


        // ------------------------------
        // Determine CC (prod only)
        // ------------------------------
        $cc = [];
        if (app()->environment('prod') && Auth::check() && validEmail(Auth::user()->email))
            $cc[] = Auth::user()->email;

        // ------------------------------
        // Send email
        // ------------------------------
        $mail = Mail::to($emailTo);
        if (!empty($cc)) $mail->cc($cc);
        $mail->send(new \App\Mail\Comms\TodoReminder($this));
    }


    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->createdBy;
    }

    /**
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute(): string
    {
        if (empty($this->attributes['attachment']))
            return '';

        $path = "todo/{$this->attributes['attachment']}";

        return FileBank::exists($path) ? FileBank::url($path) : '';
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