<?php

namespace App\Models\User;

use DB;
use URL;
use Mail;
use App\User;
use App\Models\Comms\Todo;
use App\Models\Company\Company;
use Carbon\Carbon;
use nilsenj\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserDoc extends Model {

    protected $table = 'user_docs';
    protected $fillable = [
        'type', 'category_id', 'name', 'attachment', 'issued', 'expiry', 'ref_no', 'ref_name', 'ref_type',
        'version', 'private', 'share', 'notes', 'user_id', 'company_id',
        'status', 'created_by', 'updated_by', 'approved_by', 'approved_at'];
    protected $dates = ['expiry', 'issued', 'approved_at'];


    /**
     * A User Doc belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * A User Doc is for a specific company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A User Doc is owned by a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A User Doc  was created by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A Document belongs to a Category.  (sometimes - Some Reports do)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\User\UserDocCategory', 'category_id');
    }


    /**
     * Create ToDoo for User Doc to be approved and assign to given user(s)
     */
    public function createApprovalToDo($user_list)
    {
        $user = User::findOrFail($this->user_id);
        $todo_request = [
            'type'       => 'user doc',
            'type_id'    => $this->id,
            'name'       => 'User Document Approval Request - ' . $user->name . ' (' . $this->company->name . ')',
            'info'       => 'Please approve/reject uploaded document',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->company_id,
        ];

        // Create ToDoo and assign to Userlist
        if ($user_list) {
            $todo = Todo::create($todo_request);
            $todo->assignUsers($user_list);
            $todo->emailToDo();
        }

    }

    /**
     * Create ToDoo for Expired User Doc to be sent to company
     */
    public function createExpiredToDo($user_list, $expired)
    {
        $mesg = ($expired == true) ? "$this->name Expired " . $this->expiry->format('d/m/Y') : "$this->name due to expire " . $this->expiry->format('d/m/Y');
        $todo_request = [
            'type'       => 'user doc',
            'type_id'    => $this->id,
            'name'       => $mesg,
            'info'       => 'Please uploaded a current version of the document',
            'due_at'     => Carbon::today()->addDays(7)->toDateTimeString(),
            'company_id' => $this->company_id,
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
            $user = Auth::user();

        // Get a list of Document ID's of same type as this Document ie Workers Comp
        // so we can close any ToDoo related to this type of document
        $similiar_docs = DB::table('user_docs')->select('id')->where('category_id', $this->category_id)->where('user_id', $this->user_id)->where('company_id', $this->company_id)->get();
        $id_array = [];
        foreach ($similiar_docs as $doc)
            $id_array[] = $doc->id;

        $todos = Todo::where('type', 'user doc')->whereIn('type_id', $id_array)->where('status', '1')->get();
        foreach ($todos as $todo) {
            Toastr::success("Close ToDo $todo->id");
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = $user->id;
            $todo->save();
        }
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
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\User\UserDocRejected($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\User\UserDocRejected($this));
    }

    /**
     * Email document as Expired
     */
    public function emailExpired($email_to = '', $expired)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';
        if (\App::environment('prod')) {
            // Send to Company Senior Users
            $email_to = $this->company->seniorUsersEmail();
            // Send CC to Parent Company if doc type acc or whs
            if ($this->category->type == 'acc' || $this->category->type == 'whs')
                $email_user = $this->owned_by->notificationsUsersEmailType('doc.' . $this->category->type . '.approval');
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->send(new \App\Mail\User\UserDocExpired($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\User\UserDocExpired($this));
    }




    /**
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        //$url = URL::to('/filebank') . '/company/' . $this->company->id . '/docs/' . $this->attributes['attachment'];
        if ($this->attributes['attachment'])// && file_exists(public_path('/filebank/company/' . $this->company->id . '/docs/' . $this->attributes['attachment'])))
            return '/filebank/user/' . $this->user_id . '/docs/' . $this->attributes['attachment'];

        return '';
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

