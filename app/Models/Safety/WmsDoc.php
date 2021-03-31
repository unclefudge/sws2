<?php

namespace App\Models\Safety;

use URL;
use Mail;
use App\User;
use App\Models\Company\Company;
use App\Models\Comms\Todo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WmsDoc extends Model {

    protected $table = 'wms_docs';
    protected $fillable = [
        'name', 'project', 'attachment', 'reference', 'version', 'builder', 'master', 'master_id',
        'principle', 'principle_id', 'principle_signed_id', 'principle_signed_at', 'res_compliance', 'res_review',
        'user_signed_id', 'user_signed_at', 'notes', 'for_company_id', 'company_id',
        'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $dates = ['principle_signed_at', 'user_signed_at'];

    /**
     * A WMS Doc has many Steps.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function steps()
    {
        return $this->hasMany('App\Models\Safety\WmsStep', 'doc_id');
    }

    /**
     * A WMS Doc is owned by a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A WMS Doc is for a specific company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company', 'for_company_id');
    }

    /**
     * A WMS Doc 'may' have a Principle company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function principleCompany()
    {
        return $this->belongsTo('App\Models\Company\Company', 'principle_id');
    }

    /**
     * A WMS Doc 'may' have been signed by a Principle company user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedPrinciple()
    {
        return $this->belongsTo('App\User', 'principle_signed_id');
    }

    /**
     * A WMS Doc 'may' have been signed by a company user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedCompany()
    {
        return $this->belongsTo('App\User', 'user_signed_id');
    }

    /**
     * Create ToDoo for Expired SWMS to be sent to company
     */
    public function createExpiredToDo($user_list, $expired)
    {
        $mesg = ($expired == true) ? "SWMS - $this->name Expired " . $this->created_at->addYear()->format('d/m/Y') : "SWMS - $this->name due to expire " . $this->created_at->addYear()->format('d/m/Y');
        $todo_request = [
            'type'       => 'swms',
            'type_id'    => $this->id,
            'name'       => $mesg,
            'info'       => 'Please create a new SWMS to replace the current document.',
            'due_at'     => Carbon::today()->addDays(7)->toDateTimeString(),
            'company_id' => $this->company_id,
        ];

        // Create ToDoo and assign to Site Supervisors
        if ($user_list) {
            $todo = Todo::create($todo_request);
            $todo->assignUsers($user_list);
            $todo->emailToDo();
        }
    }

    /**
     * Close any outstanding ToDoo for this Doc
     */
    public function closeToDo($user = '')
    {
        if (!$user)
            $user = Auth::user();

        $todos = Todo::where('type', 'swms')->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = $user->id;
            $todo->save();
        }
    }

    /**
     * Email document to someone
     */
    public function emailStatement($email_list, $email_user = false)
    {
        $email_list = explode(';', $email_list);
        $email_list = array_map('trim', $email_list); // trim white spaces
        $email_user = (\App::environment('dev', 'prod') && $email_user) ? Auth::user()->email : '';

        $data = [
            'user_email'        => Auth::user()->email,
            'user_fullname'     => Auth::user()->fullname,
            'user_company_name' => Auth::user()->company->name,
            'doc_name'          => $this->name,
            'doc_company'       => Company::find($this->for_company_id)->name,
            'doc_principle'     => $this->principle,
        ];
        $doc = $this;
        Mail::send('emails/workmethod', $data, function ($m) use ($email_list, $email_user, $doc, $data) {
            $user_email = $data['user_email'];
            ($user_email) ? $send_from = $user_email : $send_from = 'do-not-reply@safeworksite.com.au';

            $m->from($send_from, Auth::user()->fullname);
            $m->to($email_list);
            if (validEmail($email_user))
                $m->cc($email_user);
            $m->subject('Safe Work Method Statement - ' . $doc->name);
            $file_path = public_path('filebank/company/' . $doc->for_company_id . '/wms/' . $doc->attachment);
            if ($doc->attachment && file_exists($file_path))
                $m->attach($file_path);
        });

        /*
        if (count(Mail::failures()) > 0) {
            foreach (Mail::failures as $email_address)
                Toastr::error("Failed to send to $email_address");
        } else
            Toastr::success("Sent email");
        */
    }

    /**
     * Email document to someone for Sign Off
     */
    public function emailSignOff()
    {
        $email_to = [env('EMAIL_ME')];
        $email_user = '';
        if (\App::environment('dev', 'prod')) {
            $email_to = $this->owned_by->notificationsUsersEmailType('swms.approval');
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        $data = [
            'user_email'        => Auth::user()->email,
            'user_fullname'     => Auth::user()->fullname,
            'user_company_name' => Auth::user()->company->name,
            'doc_name'          => $this->name,
            'doc_company'       => Company::find($this->for_company_id)->name,
            'doc_principle'     => $this->principle,
        ];
        $doc = $this;
        Mail::send('emails/workmethod-signoff', $data, function ($m) use ($email_to, $email_user, $doc, $data) {
            ($email_user) ? $send_from = $email_user : $send_from = 'do-not-reply@safeworksite.com.au';
            $m->from($send_from, Auth::user()->fullname);
            $m->to($email_to);
            if ($email_user)
                $m->cc($email_user);
            $m->subject('Safe Work Method Statement - ' . $doc->name);
            $file_path = public_path($doc->attachmentUrl);
            if ($doc->attachment && file_exists($file_path))
                $m->attach($file_path);
        });
    }

    /**
     * Email document to someone
     */
    public function emailArchived()
    {
        $email_to = [env('EMAIL_ME')];
        $email_user = '';
        if (\App::environment('dev', 'prod')) {
            $email_to = $this->owned_by->notificationsUsersEmailType('doc.whs.approval');
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        $data = [
            'user_email'        => Auth::user()->email,
            'user_fullname'     => Auth::user()->fullname,
            'user_company_name' => Auth::user()->company->name,
            'doc_name'          => $this->name,
            'doc_company'       => Company::find($this->for_company_id)->name,
            'doc_principle'     => $this->principle,
        ];
        $doc = $this;
        Mail::send('emails/workmethod-archived', $data, function ($m) use ($email_to, $email_user, $doc, $data) {
            ($email_user) ? $send_from = $email_user : $send_from = 'do-not-reply@safeworksite.com.au';
            $m->from($send_from, Auth::user()->fullname);
            $m->to($email_to);
            if ($email_user)
                $m->cc($email_user);
            $m->subject('Safe Work Method Statement - ' . $doc->name);
            $file_path = public_path($doc->attachmentUrl);
            if ($doc->attachment && file_exists($file_path))
                $m->attach($file_path);
        });

        /*
        if (count(Mail::failures()) > 0) {
            foreach (Mail::failures as $email_address)
                Toastr::error("Failed to send to $email_address");
        } else
            Toastr::success("Sent email");
        */
    }

    /**
     * Email document as Rejected
     */
    public function emailExpired($email_to = '', $expired)
    {
        $company = Company::find($this->for_company_id);
        $email_to = [env('EMAIL_ME')];
        $email_user = [env('EMAIL_ME')];

        if (\App::environment('prod')) {
            if (!$email_to) {
                $email_to = [];
                $email_to[] = $company->seniorUsersEmail();

                // Send CC to Parent Company Account
                $email_user = $company->reportsTo()->notificationsUsersEmailType('doc.whs.approval');
            }
        }

        $mesg = ($expired == true) ? "has Expired " . $this->updated_at->addYear()->format('d/m/Y') : "due to expire " . $this->updated_at->addYear()->format('d/m/Y');

        $data = [
            'user_email'        => 'do-not-reply@safeworksite.com.au',
            'user_fullname'     => 'Safeworksite',
            'user_company_name' => 'Safeworksite',
            'company_name'      => $company->name,
            'doc_name'          => $this->name,
            'mesg'              => $mesg,
            'url'               => URL::to('/safety/doc/wms') . '/' . $this->id,
        ];
        $doc = $this;
        Mail::send('emails/workmethod-expired', $data, function ($m) use ($email_to, $email_user, $doc, $mesg, $data) {
            $m->from('do-not-reply@safeworksite.com.au');
            $m->to($email_to);
            if ($email_user)
                $m->cc($email_user);
            $m->subject('SWMS - ' . $doc->name . ' ' . $mesg);
        });
    }


    /**
     * Get the PrincipleName (setter)
     */
    public function getPrincipleNameAttribute()
    {
        if ($this->attributes['principle'])
            return $this->attributes['principle'];
        else if ($this->attributes['principle_id']) {
            $company = Company::findOrFail($this->attributes['principle_id']);

            return $company->name;
        }

        return;
    }

    /**
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'])
            return '/filebank/company/'.$this->attributes['for_company_id']."/wms/".$this->attributes['attachment'];
        return '';
    }


    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    /*public function getOwnedByAttribute()
    {
        if ($this->principleCompany)
            return $this->principleCompany;

        return $this->company;
    }*/

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

