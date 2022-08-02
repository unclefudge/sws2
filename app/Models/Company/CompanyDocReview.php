<?php

namespace App\Models\Company;

use DB;
use URL;
use Mail;
use App\User;
use App\Models\Comms\Todo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CompanyDocReview extends Model {

    protected $table = 'company_docs_review';
    protected $fillable = [
        'doc_id', 'todo_id', 'name', 'approved_con', 'approved_eng', 'approved_adm', 'stage', 'original_doc', 'current_doc',
        'notes', 'status', 'created_by', 'updated_by'];
    protected $dates = ['approved_con', 'approved_eng', 'approved_adm'];


    /**
     * A CompanyDocReview is for a CompanyDoc.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company_doc()
    {
        return $this->belongsTo('App\Models\Company\CompanyDoc', 'doc_id');
    }

    /**
     * A CompanyDocReview has many CompanyDocReviewFiles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany('App\Models\Company\CompanyDocReviewFile', 'review_id');
    }

    /**
     * A CompanyDocReview has many Actions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }

    /**
     * A CompanyReviewDoc 'may' have multiple ToDoos
     *
     * @return Collection
     */
    public function todos($status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'company doc review')->where('type_id', $this->id)->get();

        return Todo::where('type', 'company doc review')->where('type_id', $this->id)->get();
    }

    /**
     * A CompanyDocReview 'may' be assigned to a User
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function assignedTo()
    {
        $todo = $this->todos('1')->first();
        if ($todo)
            return $todo->assignedTo();

        return null;
    }

    /**
     * A CompanyDocReview 'may' be assigned to a User
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function assignedToSBC()
    {
        $todo = $this->todos('1')->first();
        if ($todo)
            return $todo->assignedToBySBC();

        return null;
    }

    /**
     * A list of Actions in 'Note' form
     */
    public function actionNotes()
    {
        $string = '';

        if ($this->actions) {
            $string = "<br><br><b>Notes</b><br>";
            foreach ($this->actions as $action) {
                $string .= $action->created_at->format('d/m/Y') . " &nbsp; - &nbsp; " . $action->action . " &nbsp; (" . $action->user->fullname . ")<br>";
            }
        }

        return $string;
    }


    /**
     * A CompanyDocReview was updated by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo('App\User', 'updated_by');
    }


    /**
     * Create ToDoo for CompanyDocReview to be approved and assign to given user(s)
     */
    public function createAssignToDo($user_list, $due_at = null)
    {
        $due_date = ($due_at) ? Carbon::createFromFormat('d/m/Y H:i', $due_at . '00:00')->toDateTimeString() : nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString();
        $todo_request = [
            'type'       => 'company doc review',
            'type_id'    => $this->id,
            'name'       => "Standard Details Review -  $this->name",
            'info'       => "Please review the Standard Details document".$this->actionNotes(),
            'due_at'     => $due_date,
            'company_id' => $this->company_doc->company_id,
        ];

        $this->closeToDo(); // Close any outstanding ToDos

        // Create ToDoo and assign to Userlist
        if ($user_list) {
            $todo = Todo::create($todo_request);
            $todo->assignUsers($user_list);
            $todo->emailToDo();
        }
    }

    /**
     * Create ToDoo for Expired Company Doc to be sent to company
     */
    public function createExpiredToDo($user_list, $expired)
    {
        $mesg = ($expired == true) ? "$this->name Expired " . $this->expiry->format('d/m/Y') : "$this->name due to expire " . $this->expiry->format('d/m/Y');
        $todo_request = [
            'type'       => 'company doc',
            'type_id'    => $this->id,
            'name'       => $mesg,
            'info'       => 'Please uploaded a current version of the document',
            'due_at'     => Carbon::today()->addDays(7)->toDateTimeString(),
            'company_id' => $this->company_doc->company_id,
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Close any outstanding ToDoo for this Doc
     */
    public function closeToDo($user = '')
    {
        if (!$user)
            $user = (Auth::check()) ? Auth::user() : User::find(1); // Logged in User else System;

        $todos = Todo::where('type', 'company doc review')->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = $user->id;
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
            //$email_list = $this->site->company->notificationsUsersEmailType('site.qa');
            //$email_supers = $this->site->supervisorsEmails();
            //$email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
            $email_to = $this->site->supervisorsEmails();
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
     * Email document as Rejected
     */
    public function emailReject()
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            // Send to User who uploaded doc & Company senior users
            $email_created = (validEmail($this->createdBy->email)) ? [$this->createdBy->email] : [];
            $email_seniors = []; //$this->company->seniorUsersEmail();
            $email_to = array_unique(array_merge($email_created, $email_seniors), SORT_REGULAR);
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Company\CompanyDocRejected($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Company\CompanyDocRejected($this));
    }


    /**
     * Email document to be renewed
     */
    public function emailRenewal($email_to = '')
    {
        if (!\App::environment('prod'))
            $email_to = [env('EMAIL_DEV')];

        if ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Company\CompanyDocRenewal($this));
    }


    /**
     * Get the Original Doc URL (setter)
     */
    public function getOriginalDocUrlAttribute()
    {
        if ($this->attributes['original_doc'])// && file_exists(public_path('/filebank/company/' . $this->company_doc->company_id . '/docs/' . $this->attributes['original_doc'])))
            return '/filebank/company/' . $this->company_doc->company_id . '/docs/' . $this->attributes['original_doc'];

        return '';
    }

    /**
     * Get the Current Doc URL
     */
    public function getCurrentDocUrlAttribute()
    {
        if ($this->attributes['current_doc'])// && file_exists(public_path('/filebank/company/' . $this->company->id . '/docs/' . $this->attributes['attachment'])))
            return '/filebank/company/' . $this->company_doc->company_id . '/docs/review/' . $this->attributes['current_doc'];

        if ($this->attributes['original_doc'])// && file_exists(public_path('/filebank/company/' . $this->company_doc->company_id . '/docs/' . $this->attributes['original_doc'])))
            return '/filebank/company/' . $this->company_doc->company_id . '/docs/' . $this->attributes['original_doc'];
    }

    /**
     * Get the Stage Text
     */
    public function getStageTextAttribute()
    {
        if ($this->attributes['stage'] == '1')
            return 'Initial review by Con Mgr';
        if ($this->attributes['stage'] == '2')
            return 'Document to be assigned to draftsperson';
        if ($this->attributes['stage'] == '3')
            return 'Document being updated by draftsperson';
        if ($this->attributes['stage'] == '4')
            return 'Document review by Con Mgr';
        if ($this->attributes['stage'] == '9')
            return 'Review completed: assign renewal date';
        if ($this->attributes['stage'] == '10')
            return 'Review completed';
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

