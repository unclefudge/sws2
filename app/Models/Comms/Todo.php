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
        return Attachment::where('table', $this->table)->where('table_id', $this->id)->get();
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
        foreach ($this->assignedTo() as $user) {
            $string .= $user->fullname . ', ';
        }
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
        foreach ($this->assignedTo() as $user) {
            $string .= $user->fullname . ' (' . $user->company->name . '), ';
        }
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


    /**
     * A Todoo is often linked to a webpage
     *
     * @return url
     */
    public function url()
    {
        switch ($this->type) {
            case 'toolbox':
                return "/safety/doc/toolbox2/$this->type_id";
            case 'qa':
                return "/site/qa/$this->type_id";
            case 'maintenance':
                return "/site/maintenance/$this->type_id";
            case 'maintenance_item':
                $item = SiteMaintenanceItem::find($this->type_id);
                if ($item && $item->maintenance)
                    return '/site/maintenance/' . $item->maintenance->id;
            case 'prac_completion':
                return "/site/prac-completion/$this->type_id";
            case 'foc':
                return "/site/foc/$this->type_id";
            case 'inspection_electrical':
                return "/site/inspection/electrical/$this->type_id";
            case 'inspection_plumbing':
                return "/site/inspection/plumbing/$this->type_id";
            case 'project supply':
                return "/site/supply/$this->type_id/edit";
            case 'dial_before_dig':
                return "/site/doc";
            case 'site shutdown':
                return "/site/shutdown/$this->type_id/edit";
            case 'super checklist':
                return "/supervisor/checklist/$this->type_id/$this->type_id2";
            case 'super checklist signoff':
                return "/supervisor/checklist/$this->type_id/weekly";
            case 'scaffold handover':
                return "/site/scaffold/handover/$this->type_id/edit";
            case 'extension':
                return "/site/extension";
            case 'extension signoff':
                return "/site/extension";
            case 'asbestos notify':
                return "/site/asbestos/notification/$this->type_id/edit";
            case 'incident review':
                return "/site/incident/$this->type_id";
            case 'incident witness':
                $witness = SiteIncidentWitness::find($this->type_id);
                if ($witness)
                    return '/site/incident/' . $witness->incident->id . '/witness/' . $this->type_id;
            case 'company doc':
                $doc = CompanyDoc::find($this->type_id);
                if ($doc)
                    return ($doc->expiry && $doc->expiry->gt(Carbon::today())) ? '/company/' . $doc->for_company_id . '/doc/' . $doc->id . '/edit' : '/company/' . $doc->for_company_id . '/doc';
            case 'company doc review':
                $doc = CompanyDocReview::find($this->type_id);
                if ($doc)
                    return "/company/doc/standard/review/$doc->id/edit";
            case 'company ptc':
                $ptc = CompanyDocPeriodTrade::find($this->type_id);
                if ($ptc)
                    return "/company/$ptc->for_company_id/doc/period-trade-contract/$this->type_id";
            default:
                return "/todo/$this->id";
        }

        return '';
    }

    public function record()
    {
        $status = ['1', '2', '3'];
        $task_type = $this->type;
        $type_id = $this->type_id;
        $type_id2 = $this->type_id2;
        if (in_array($task_type, ['incident', 'incident prevent', 'incident review'])) return SiteIncident::find($type_id);
        if ($task_type == 'accident') return null;
        if ($task_type == 'hazard') return SiteHazard::find($type_id);
        if ($task_type == 'maintenance') return SiteMaintenance::find($type_id);
        if ($task_type == 'maintenance_item') return SiteMaintenanceItem::find($type_id)->maintenance;
        if ($task_type == 'inspection_electrical') return SiteInspectionElectrical::find($type_id);
        if ($task_type == 'inspection_plumbing') return SiteInspectionPlumbing::find($type_id);
        if (in_array($task_type, ['super checklist', 'super checkist signoff'])) return SuperChecklist::find($type_id);
        if ($task_type == 'supervisor') return null;
        if ($task_type == 'scaffold handover') return SiteScaffoldHandover::find($type_id);
        if ($task_type == 'project supply') return SiteProjectSupply::find($type_id);
        if ($task_type == 'site shutdown') return SiteShutdown::find($type_id);
        if (in_array($task_type, ['extension', 'extension signoff'])) return SiteExtension::find($type_id);
        if ($task_type == 'equipment') return EquipmentLocation::find($type_id);
        if ($task_type == 'qa') return SiteQa::find($type_id);
        if ($task_type == 'toolbox') return ToolboxTalk::find($type_id);
        if ($task_type == 'swms') return WmsDoc::find($type_id);
        if ($task_type == 'company doc') return CompanyDoc::find($type_id);
        if ($task_type == 'company doc review') return CompanyDocReview::find($type_id);
        if ($task_type == 'user doc') return UserDoc::find($type_id);

        return null;

        /*
          'inspection' => 'Site Inspection',
          'company ptc' => 'Period Trade Contract',
          'company privacy' => 'Company Privacy Policy',
          'company doc review' => 'Standard Details Review',
          'user doc' => 'User Documents',]);
        */
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
     * Save attached Media to existing Issue
     */
    public function saveAttachedMedia($file)
    {
        /*
        $site = Site::findOrFail($this->site_id);
        $path = "filebank/site/" . $site->id . '/issue';
        $name = 'issue-' . $site->code . '-' . $this->id . '-' . Auth::user()->id . '-' . sha1(time()) . '.' . strtolower($file->getClientOriginalExtension());
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
        } else
            Toastr::error("Bad image");

        $this->attachment = $name;
        $this->save();
        */
    }

    /**
     * Email ToDoo
     */
    public function emailToDo($email_to = '', $email_cc = '')
    {
        if (\App::environment('prod')) {
            if (!$email_to || $email_to == 'ASSIGNED') {
                $email_to = [];
                foreach ($this->assignedTo() as $user) {
                    if (validEmail($user->email))
                        $email_to[] = $user->email;
                }
            }
        } else if (\App::environment('local', 'dev'))
            $email_to = [env('EMAIL_ME')];


        $cc = (\App::environment('prod') && Auth::check() && validEmail(Auth::user()->email)) ? [Auth::user()->email] : [];

        // Exclude CC for users on certain ToDoo types
        $excludeCCtypes = ['inspection_plumbing', 'inspection_electrical', 'toolbox', 'extension signoff', 'scaffold handover', 'maintenance'];
        if (in_array($this->type, $excludeCCtypes))
            $cc = [];

        // Don't cc email to user if Todoo is a Company Doc Approval
        if (preg_match('/^Company Document Approval Request/', $this->name))
            $cc = [];

        // Include specified CC'ed users in arg
        if (\App::environment('prod') && $email_cc) {
            if (is_array($email_cc)) {
                $cc = array_merge($cc, $email_cc);
            } else
                $cc[] = $email_cc;
        }

        if ($email_to && $cc)
            Mail::to($email_to)->cc($cc)->send(new \App\Mail\Comms\TodoCreated($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Comms\TodoCreated($this));
    }

    /**
     * Email ToDoo
     */
    public function emailToDoCompleted($email_to = '')
    {
        if (\App::environment('prod')) {
            if (!$email_to) {
                $email_to = [];
                foreach ($this->assignedTo() as $user) {
                    if (validEmail($user->email))
                        $email_to[] = $user->email;
                }
            }
        } else if (\App::environment('local', 'dev'))
            $email_to = [env('EMAIL_ME')];

        $email_user = (\App::environment('prod')) ? Auth::user()->email : '';

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Comms\TodoCompleted($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Comms\TodoCompleted($this));
    }

    public function emailToDoReminder($email_to = '')
    {
        if (\App::environment('prod')) {
            if (!$email_to) {
                $email_to = [];
                foreach ($this->assignedTo() as $user) {
                    if (validEmail($user->email))
                        $email_to[] = $user->email;
                }
            }
        } else if (\App::environment('local', 'dev'))
            $email_to = [env('EMAIL_ME')];

        $email_user = (\App::environment('prod')) ? Auth::user()->email : '';

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Comms\TodoReminder($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Comms\TodoReminder($this));
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
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'] && file_exists(public_path('/filebank/todo/' . $this->attributes['attachment'])))
            return '/filebank/todo/' . $this->attributes['attachment'];

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