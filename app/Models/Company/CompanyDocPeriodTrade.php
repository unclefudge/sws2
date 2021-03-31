<?php

namespace App\Models\Company;

use DB;
use URL;
use Mail;
use App\User;
use App\Models\Comms\Todo;
use App\Models\Company\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CompanyDocPeriodTrade extends Model {

    protected $table = 'company_doc_ptc';
    protected $fillable = [
        'date', 'period', 'attachment', 'expiry', 'principle_id', 'principle_name', 'principle_address',
        'principle_phone', 'principle_email', 'principle_abn', 'principle_hia', 'principle_signed_id', 'principle_signed_at', 'principle_signed_name',
        'contractor_id', 'contractor_name', 'contractor_address', 'contractor_phone', 'contractor_email', 'contractor_abn', 'contractor_gst', 'contractor_licence',
        'contractor_pl_name', 'contractor_pl_ref', 'contractor_pl_expiry', 'contractor_wc_name', 'contractor_wc_ref', 'contractor_wc_expiry',
        'contractor_sa_name', 'contractor_sa_ref', 'contractor_sa_expiry', 'contractor_signed_id', 'contractor_signed_at', 'contractor_signed_name',
        'share', 'notes', 'for_company_id', 'company_id', 'status', 'created_by', 'updated_by'];
    protected $dates = ['date', 'expiry', 'contractor_pl_expiry', 'contractor_sa_expiry', 'contractor_wc_expiry', 'principle_signed_at', 'contractor_signed_at'];


    /**
     * A PTC Doc is for a specific company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company', 'for_company_id');
    }

    /**
     * A PTC Doc 'may' have a Principle company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function principleCompany()
    {
        return $this->belongsTo('App\Models\Company\Company', 'principle_id');
    }

    /**
     * A PTC Doc 'may' have been signed by a Principle company user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedPrinciple()
    {
        return $this->belongsTo('App\User', 'principle_signed_id');
    }

    /**
     * A PTC Doc 'may' have been signed by a company user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedCompany()
    {
        return $this->belongsTo('App\User', 'user_signed_id');
    }

    /**
     * A PTC Doc is owned by a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A PTC Doc  was created by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * Create ToDoo for PTC Doc to be approved and assign to given user(s)
     */
    public function createApprovalToDo($user_list)
    {
        $company = Company::findOrFail($this->for_company_id);
        $todo_request = [
            'type'       => 'company ptc',
            'type_id'    => $this->id,
            'name'       => 'Company Period Trade Contract Sign Off - ' . $company->name,
            'info'       => 'Please sign/reject document',
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
     * Create ToDoo for Expired PTC Doc to be sent to company
     */
    /*
    public function createExpiredToDo($user_list, $expired)
    {
        $mesg = ($expired == true) ? "$this->name Expired " . $this->expiry->format('d/m/Y') : "$this->name due to expire " . $this->expiry->format('d/m/Y');
        $todo_request = [
            'type'       => 'company ptc',
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
    }*/

    /**
     * Close any outstanding ToDoo for this Doc
     */
    public function closeToDo($user = '')
    {
        if (!$user)
            $user = Auth::user();

        $todos = Todo::where('type', 'company ptc')->where('type_id', $this->id)->where('status', '1')->get();
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
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Company\CompanyPeriodTradeRejected($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Company\CompanyPeriodTradeRejected($this));
    }

    /**
     * Email document as Expired
     */
    /*
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
            Mail::to($email_to)->send(new \App\Mail\Company\CompanyDocExpired($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Company\CompanyDocExpired($this));
    }*/


    /**
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        //$url = URL::to('/filebank') . '/company/' . $this->company->id . '/docs/' . $this->attributes['attachment'];
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

