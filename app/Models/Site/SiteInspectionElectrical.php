<?php

namespace App\Models\Site;

use App\Models\Comms\Todo;
use App\Models\Misc\TemporaryFile;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;

class SiteInspectionElectrical extends Model
{

    protected $table = 'site_inspection_electrical';
    protected $fillable = [
        'site_id', 'client_name', 'client_address', 'client_contacted', 'info', 'assigned_to', 'assigned_at', 'inspected_by', 'inspected_at', 'inspected_name', 'inspected_lic',
        'existing', 'required', 'required_cost', 'recommend', 'recommend_cost', 'trade_notes',
        'supervisor_sign_by', 'supervisor_sign_at', 'manager_sign_by', 'manager_sign_at',
        'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    protected $dates = ['client_contacted', 'inspected_at', 'assigned_at', 'supervisor_sign_at', 'manager_sign_at',];

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

    /**
     * A SiteInspectionElectrical belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SiteInspectionElectrical assigned to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function assignedTo()
    {
        return $this->belongsTo('App\Models\Company\Company', 'assigned_to');
    }

    /**
     * A SiteInspectionElectrical has many Docs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function docs()
    {
        return SiteInspectionDoc::where('inspect_id', $this->id)->where('table', 'electrical')->get();
    }

    /**
     * A SiteInspectionElectrical has many Actions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }

    /**
     * A SiteInspectionElectrical belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * Save attached Media to existing Issue
     */
    public function saveAttachment($tmp_filename)
    {
        $tempFile = TemporaryFile::where('folder', $tmp_filename)->first();
        if ($tempFile) {
            // Move temp file to support ticket directory
            $dir = "filebank/site/" . $this->site_id . '/inspection';
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

            $tempFilePublicPath = public_path($tempFile->folder) . "/" . $tempFile->filename;
            if (file_exists($tempFilePublicPath)) {
                $newFile = $this->site_id . '-' . $tempFile->filename;

                // Ensure filename is unique by adding counter to similiar filenames
                $count = 1;
                while (file_exists(public_path("$dir/$newFile"))) {
                    $ext = pathinfo($newFile, PATHINFO_EXTENSION);
                    $filename = pathinfo($newFile, PATHINFO_FILENAME);
                    $newFile = $filename . $count++ . ".$ext";
                }
                rename($tempFilePublicPath, public_path("$dir/$newFile"));

                // Determine file extension and set type
                $ext = pathinfo($tempFile->filename, PATHINFO_EXTENSION);
                $orig_filename = pathinfo($tempFile->filename, PATHINFO_BASENAME);
                $type = (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'])) ? 'image' : 'file';
                $new = SiteInspectionDoc::create(['inspect_id' => $this->id, 'table' => 'electrical', 'type' => $type, 'name' => $orig_filename, 'attachment' => $newFile]);
            }

            // Delete Temporary file directory + record
            $tempFile->delete();
            rmdir(public_path($tempFile->folder));
        }
    }

    /**
     * Create ToDoo for Electrical Report and assign to given user(s)
     */
    public function createAssignedToDo($user_list)
    {
        $todo_request = [
            'type' => 'inspection_electrical',
            'type_id' => $this->id,
            'name' => 'Electrical Inspection Report - ' . $this->site->name,
            'info' => 'Please complete the inspection report',
            'priority' => '1',
            'due_at' => nextWorkDate(Carbon::today(), '+', 15)->toDateTimeString(),
            'company_id' => '3',
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Create ToDoo for Electrical Report and assign to given user(s)
     */
    public function createConstructionToDo($user_list)
    {
        // Create ToDoo for Construction Manager to assign to company
        $todo_request = [
            'type' => 'inspection_electrical',
            'type_id' => $this->id,
            'name' => 'Electrical Inspection Report Created - ' . $this->site->name,
            'info' => 'Please review inspection and assign to a company',
            'due_at' => nextWorkDate(Carbon::today(), '+', 1)->toDateTimeString(),
            'company_id' => $this->site->owned_by->id,
        ];

        // Create ToDoo and assign to Construction Manager
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
    }

    /**
     * Create ToDoo for Electrical Report and assign to given user(s)
     */
    public function createSignOffToDo($user_list)
    {
        // Create ToDoo for Construction Manager to review report
        $todo_request = [
            'type' => 'inspection_electrical',
            'type_id' => $this->id,
            'name' => 'Electrical Inspection Report Completed - ' . $this->site->name,
            'info' => 'Please review the Report and sign off on the Task',
            'due_at' => nextWorkDate(Carbon::today(), '+', 1)->toDateTimeString(),
            'company_id' => $this->site->owned_by->id,
        ];

        // Create ToDoo and assign to Review Manager
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Create ToDoo for Electrical Report and assign to given user(s)
     */
    public function createElectricalReviewToDo($user_list)
    {
        // Create ToDoo for Electrical Reviewer to review report
        $todo_request = [
            'type' => 'inspection_electrical',
            'type_id' => $this->id,
            'name' => 'Electrical Inspection Report Completed - ' . $this->site->name,
            'info' => 'Please process the Variation and sign off on the Task',
            'due_at' => nextWorkDate(Carbon::today(), '+', 1)->toDateTimeString(),
            'company_id' => $this->site->owned_by->id,
        ];

        // Create ToDoo and assign to Construction Manager
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
    }

    /**
     * Close any outstanding ToDoo for this QA
     */
    public function closeToDo()
    {
        $todos = Todo::where('type', 'inspection_electrical')->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = (Auth::check()) ? Auth::user()->id : 1;
            $todo->save();
        }
    }

    /**
     * Email Action Notification
     */
    public function emailAction($action, $important = false)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = $this->site->supervisorEmail;
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        /*
        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteMaintenanceAction($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteMaintenanceAction($this, $action));
        */
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
}