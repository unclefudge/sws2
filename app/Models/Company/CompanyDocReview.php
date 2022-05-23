<?php

namespace App\Models\Company;

use DB;
use URL;
use Mail;
use App\User;
use App\Models\Comms\Todo;
use App\Models\Misc\ContractorLicence;
use App\Models\Misc\ContractorLicenceSupervisor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CompanyDocReview extends Model {

    protected $table = 'company_docs_review';
    protected $fillable = [
        'doc_id', 'todo_id', 'name', 'stage', 'original_doc', 'current_doc',
        'notes', 'status', 'created_by', 'updated_by'];
    //protected $dates = ['expiry', 'approved_at'];


    /**
     * A CompanyReviewDoc is for a CompanyDoc.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company_doc()
    {
        return $this->belongsTo('App\Models\Company\CompanyDoc', 'doc_id');
    }


    /**
     * A Company Doc was updated by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo('App\User', 'updated_by');
    }

    /**
     * A Document belongs to a Category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Company\CompanyDocCategory', 'category_id');
    }


    /**
     * Create ToDoo for Company Doc to be approved and assign to given user(s)
     */
    public function createAssignToDo($user_list)
    {
        $todo_request = [
            'type'       => 'company doc review',
            'type_id'    => $this->id,
            'name'       => "Standard Details Review -  $this->name",
            'info'       => 'Please review the Standard Details document',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->company_doc->company_id,
        ];

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
            $user = Auth::user();

        // Get a list of Document ID's of same type as this Document ie Workers Comp
        // so we can close any ToDoo related to this type of document
        $similiar_docs = DB::table('company_docs')->select('id')->where('category_id', $this->category_id)->where('for_company_id', $this->for_company_id)->where('company_id', $this->company_id)->get();
        $id_array = [];
        foreach ($similiar_docs as $doc)
            $id_array[] = $doc->id;

        $todos = Todo::where('type', 'company doc')->whereIn('type_id', $id_array)->where('status', '1')->get();
        foreach ($todos as $todo) {
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
     * Get the Attachment URL (setter)
     */
    public function getCurrentDocUrlAttribute()
    {
        if ($this->attributes['current_doc'])// && file_exists(public_path('/filebank/company/' . $this->company->id . '/docs/' . $this->attributes['attachment'])))
            return '/filebank/company/' . $this->company->id . '/docs/review/' . $this->attributes['current_doc'];

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

