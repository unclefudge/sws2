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
use App\Models\Site\Incident\SiteIncidentWitness;
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
use App\Support\TodoTypeRegistry;
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
        return TodoTypeRegistry::record($this->type, (int)$this->type_id);
    }

    /**
     * A Todoo is often linked to a webpage
     *
     * @return url
     */
    public function url(): string
    {
        return TodoTypeRegistry::url($this);
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
        if (app()->environment('prod')) {
            if (!$emailTo) {
                $emailTo = collect($this->assignedTo())->pluck('email')->filter(fn($email) => validEmail($email))->values()->all();
            }

        } else
            $emailTo = [env('EMAIL_ME')];

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

    public function emailToDoReminder($emailTo = null): void
    {
        if (app()->environment('prod')) {
            // Use supplied recipient(s), otherwise the assigned users
            if (empty($emailTo)) {
                $emailTo = $this->assignedTo()->pluck('email')->all();
            }
        } else {
            // Local/dev emails always go to you
            $emailTo = config('mail.email_me');
        }

        // Normalise and validate recipients
        $emailTo = collect(is_array($emailTo) ? $emailTo : [$emailTo])->filter(fn($email) => is_string($email) && validEmail($email))->unique()->values()->all();
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
        if (!empty($cc)) {
            $mail->cc($cc);
        }
        $mail->send(new \App\Mail\Comms\TodoReminder($this));
    }

    /*public function emailToDoReminder2($emailTo = [])
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
    }*/


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