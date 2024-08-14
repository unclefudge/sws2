<?php

namespace App\Models\Company;

use App\Models\Comms\Todo;
use App\Models\Misc\ContractorLicence;
use App\Models\Misc\ContractorLicenceSupervisor;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class CompanyDoc extends Model
{

    protected $table = 'company_docs';
    protected $fillable = [
        'type', 'category_id', 'name', 'attachment', 'expiry', 'ref_no', 'ref_name', 'ref_type',
        'version', 'private', 'share', 'notes', 'for_company_id', 'company_id',
        'status', 'created_by', 'updated_by', 'approved_by', 'approved_at'];
    protected $casts = ['expiry' => 'datetime', 'approved_at' => 'datetime'];


    /**
     * A Company Doc is for a specific company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company', 'for_company_id');
    }

    /**
     * A Company Doc is owned by a company.
     *
     * @return \Illumiemnate\Database\Eloquent\Relations\belongsTo
     */
    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }


    /**
     * A Company Doc  was created by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A Company Doc was approved by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function approvedBy()
    {
        return $this->belongsTo('App\User', 'approved_by');
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
     * Contract Licence supervisor id
     */
    public function contractorLicenceSupervisor($super = 1)
    {
        if ($this->category_id == 7) {
            $rec = ContractorLicenceSupervisor::where('doc_id', $this->id)->where('super', $super)->first();
            if ($rec)
                return $rec->user_id;
        }

        return null;
    }

    /**
     * A Document MAY have many users assigned as supervisor
     *
     */
    public function contractorLicenceSupervisorClasses($super = 1)
    {
        return ContractorLicenceSupervisor::where('doc_id', $this->id)->where('super', $super)->pluck('licence_id')->toArray();
    }

    /**
     * A Document MAY have many users assigned as supervisor - SBC
     */
    public function contractorLicenceSupervisorClassesSBC($super = 1)
    {
        $classes = ContractorLicenceSupervisor::where('doc_id', $this->id)->where('super', $super)->pluck('licence_id')->toArray();
        if ($classes) {
            $string = '';
            foreach ($classes as $class)
                $string .= ContractorLicence::find($class)->name . ', ';

            return rtrim($string, ', ');
        }

        return '';
    }

    /**
     * Create ToDoo for Company Doc to be approved and assign to given user(s)
     */
    public function createApprovalToDo($user_list)
    {
        $company = Company::findOrFail($this->for_company_id);
        $todo_request = [
            'type' => 'company doc',
            'type_id' => $this->id,
            'name' => 'Company Document Approval Request - ' . $company->name_alias . ' (' . $this->name . ')',
            'info' => 'Please approve/reject uploaded document',
            'due_at' => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->company_id,
        ];

        // Create ToDoo and assign to Userlist
        if ($user_list) {
            $todo = Todo::create($todo_request);
            $todo->assignUsers($user_list);
            $todo->emailToDo();

            if (\App::environment('prod')) {
                // Send Kirstie a copy of Company Doc Approval ToDoo task as well
                $todo->emailToDo('kirstie@capecod.com.au');
            }
        }
    }

    /**
     * Create ToDoo for Expired Company Doc to be sent to company
     */
    public function createExpiredToDo($user_list, $expired)
    {
        $mesg = ($expired == true) ? "$this->name Expired " . $this->expiry->format('d/m/Y') : "$this->name due to expire " . $this->expiry->format('d/m/Y');
        $todo_request = [
            'type' => 'company doc',
            'type_id' => $this->id,
            'name' => $mesg,
            'info' => 'Please uploaded a current version of the document',
            'due_at' => Carbon::today()->addDays(7)->toDateTimeString(),
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
            //
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
     * Email document as Expired
     */
    public function emailExpired($email_to = '', $expired = false)
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

        if (DEBUG_EMAIL) {
            $list = (is_array($email_to)) ? implode(',', $email_to) : $email_to;
            app('log')->debug("DEBUG-EMAIL: TO [$list]");
            $list = (is_array($email_user)) ? implode(',', $email_user) : $email_user;
            app('log')->debug("DEBUG-EMAIL: CC [$list]");
        }
        if ($email_to && $email_user)
            Mail::to($email_to)->cc($email_user)->send(new \App\Mail\Company\CompanyDocExpired($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Company\CompanyDocExpired($this));

    }

    /**
     * Email document to be renewed
     */
    public function emailRenewal($email_to = '')
    {
        if (!\App::environment('prod'))
            $email_to = [env('EMAIL_DEV')];

        if (validEmail($email_to))
            Mail::to($email_to)->send(new \App\Mail\Company\CompanyDocRenewal($this));
    }


    /**
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'])// && file_exists(public_path('/filebank/company/' . $this->company->id . '/docs/' . $this->attributes['attachment'])))
            return '/filebank/company/' . $this->company->id . '/docs/' . $this->attributes['attachment'];

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

