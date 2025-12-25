<?php

namespace App\Models\Safety;

use App\Models\Comms\Todo;
use App\Models\Company\Company;
use App\Services\FileBank;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class WmsDoc extends Model
{

    protected $table = 'wms_docs';
    protected $fillable = [
        'name', 'project', 'attachment', 'reference', 'version', 'builder', 'master', 'master_id',
        'principle', 'principle_id', 'principle_signed_id', 'principle_signed_at', 'res_compliance', 'res_review',
        'user_signed_id', 'user_signed_at', 'notes', 'for_company_id', 'company_id',
        'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['principle_signed_at' => 'datetime', 'user_signed_at' => 'datetime'];


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

    public function templateModified()
    {
        if ($this->builder) {
            if ($this->master_id) {
                $master = WmsDoc::find($this->master_id);
                foreach ($this->steps as $step) {
                    if ($step->master_id) {
                        $masterstep = WmsStep::find($step->master_id);
                        if ($step->name != $masterstep->name)
                            return '<span class="font-red">Yes</span>';
                    }

                    // Hazards
                    $hazards = WmsHazard::where('step_id', $step->id)->get();
                    foreach ($hazards as $hazard) {
                        if ($hazard->master_id) {
                            $masterhazard = WmsHazard::find($hazard->master_id);
                            if ($hazard->name != $masterhazard->name)
                                return '<span class="font-red">Yes</span>';
                        }
                    };
                    if (count($hazards) != WmsHazard::where('step_id', $masterstep->id)->count())
                        return '<span class="font-red">Yes</span>';

                    // Controls
                    $controls = WmsControl::where('step_id', $step->id)->get();
                    foreach ($controls as $control) {
                        if ($control->master_id) {
                            $mastercontrol = WmsControl::find($control->master_id);
                            if ($control->name != $mastercontrol->name)
                                return '<span class="font-red">Yes</span>';
                        }
                    };
                    if (count($controls) != WmsControl::where('step_id', $masterstep->id)->count())
                        return '<span class="font-red">Yes</span>';
                };

                // Not same steps as Master
                if ($this->steps()->count() != $master->steps()->count())
                    return '<span class="font-red">Yes</span>';

                // Return true if has no steps (ie blank SWMS)
                if ($this->steps()->count())
                    return 'No';
                else
                    return ($this->attachment) ? '<span class="font-yellow-gold">Custom</span>' : '<span class="font-red">Blank</span>';
            }
            return '<span class="font-red">Custom</span>';
        }
        return '<span class="font-yellow-gold">Upload</span>';

    }

    /**
     * Create ToDoo for Expired SWMS to be sent to company
     */
    public function createExpiredToDo($user_list, $expired)
    {
        $mesg = ($expired == true) ? "SWMS - $this->name Expired " . $this->created_at->addYear()->format('d/m/Y') : "SWMS - $this->name due to expire " . $this->created_at->addYear()->format('d/m/Y');
        $todo_request = [
            'type' => 'swms',
            'type_id' => $this->id,
            'name' => $mesg,
            'info' => 'Please create a new SWMS to replace the current document.',
            'due_at' => Carbon::today()->addDays(7)->toDateTimeString(),
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
        $email_list = array_map('trim', explode(';', $email_list));
        $email_user = (\App::environment('dev', 'prod') && $email_user) ? Auth::user()->email : null;

        $data = [
            'user_email' => Auth::user()->email,
            'user_fullname' => Auth::user()->fullname,
            'user_company_name' => Auth::user()->company->name,
            'doc_name' => $this->name,
            'doc_company' => Company::find($this->for_company_id)->name,
            'doc_principle' => $this->principle,
        ];

        $doc = $this;
        if (empty(array_filter($email_list)))
            return;

        Mail::send('emails/workmethod', $data, function ($m) use ($email_list, $email_user, $doc, $data) {
            $sendFrom = validEmail($data['user_email']) ? $data['user_email'] : 'do-not-reply@safeworksite.com.au';
            $m->from($sendFrom, Auth::user()->fullname);
            $m->to($email_list);

            if (validEmail($email_user))
                $m->cc($email_user);

            $m->subject('Safe Work Method Statement - ' . $doc->name);
            // Attach PDF from FileBank (Spaces-safe)
            if ($doc->attachment)
                FileBank::attachToEmail($m, "company/{$doc->for_company_id}/wms/{$doc->attachment}");
        });
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
            'user_email' => Auth::user()->email,
            'user_fullname' => Auth::user()->fullname,
            'user_company_name' => Auth::user()->company->name,
            'doc_name' => $this->name,
            'doc_company' => Company::find($this->for_company_id)->name,
            'doc_principle' => $this->principle,
            'url' => URL::to('/safety/doc/wms') . '/' . $this->id,
        ];
        $doc = $this;

        if (!$email_to) return;

        Mail::send('emails/workmethod-signoff', $data, function ($m) use ($email_to, $email_user, $doc) {
            $send_from = $email_user ?: 'do-not-reply@safeworksite.com.au';

            $m->from($send_from, Auth::user()->fullname);
            $m->to($email_to);

            if ($email_user)
                $m->cc($email_user);

            $m->subject('Safe Work Method Statement - ' . $doc->name);

            if ($doc->attachment)
                FileBank::attachToEmail($m, "company/{$doc->for_company_id}/wms/{$doc->attachment}");
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
            'user_email' => Auth::user()->email,
            'user_fullname' => Auth::user()->fullname,
            'user_company_name' => Auth::user()->company->name,
            'doc_name' => $this->name,
            'doc_company' => Company::find($this->for_company_id)->name,
            'doc_principle' => $this->principle,
            'url' => URL::to('/safety/doc/wms') . '/' . $this->id,
        ];
        $doc = $this;
        if (!$email_to) return;

        Mail::send('emails/workmethod-archived', $data, function ($m) use ($email_to, $email_user, $doc) {
            $send_from = $email_user ?: 'do-not-reply@safeworksite.com.au';
            $m->from($send_from, Auth::user()->fullname);
            $m->to($email_to);
            if ($email_user)
                $m->cc($email_user);
            $m->subject('Safe Work Method Statement - ' . $doc->name);

            if ($doc->attachment)
                FileBank::attachToEmail($m, "company/{$doc->for_company_id}/wms/{$doc->attachment}");
        });
    }

    /**
     * Email document as Rejected
     */
    public function emailExpired($email_to = '', $expired)
    {
        $company = Company::find($this->for_company_id);
        $email_to = [env('EMAIL_ME')];
        $email_user = [env('EMAIL_ME')];

        if (app()->environment('prod')) {
            if (!$email_to) {
                $email_to = [];
                $email_to[] = $company->seniorUsersEmail();

                // Send CC to Parent Company Account
                $email_user = $company->reportsTo()->notificationsUsersEmailType('doc.whs.approval');
            }
        }

        $mesg = ($expired == true) ? "has Expired " . $this->updated_at->addYear()->format('d/m/Y') : "due to expire " . $this->updated_at->addYear()->format('d/m/Y');

        $data = [
            'user_email' => 'do-not-reply@safeworksite.com.au',
            'user_fullname' => 'Safeworksite',
            'user_company_name' => 'Safeworksite',
            'company_name' => $company->name,
            'doc_name' => $this->name,
            'mesg' => $mesg,
            'url' => URL::to('/safety/doc/wms') . '/' . $this->id,
        ];
        $doc = $this;
        if ($email_to) {
            Mail::send('emails/workmethod-expired', $data, function ($m) use ($email_to, $email_user, $doc, $mesg, $data) {
                $m->from('do-not-reply@safeworksite.com.au');
                $m->to($email_to);
                if ($email_user)
                    $m->cc($email_user);
                $m->subject('SWMS - ' . $doc->name . ' ' . $mesg);
            });
        }
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


    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment) return '';
        $path = "company/$this->for_company_id/wms/$this->attachment";

        return FileBank::exists($path) ? FileBank::url($path) : '';
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

