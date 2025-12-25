<?php

namespace App\Models\Company;

use App\Models\Comms\Todo;
use App\Services\FileBank;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class CompanyDocPrivacyPolicy extends Model
{

    protected $table = 'company_doc_privacy';
    protected $fillable = [
        'date', 'attachment', 'contractor_signed_id', 'contractor_signed_at', 'contractor_signed_name',
        'share', 'notes', 'for_company_id', 'company_id', 'status', 'created_by', 'updated_by'];
    protected $casts = ['date' => 'datetime', 'contractor_signed_at' => 'datetime'];


    /**
     * A Privacy Doc is for a specific company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company', 'for_company_id');
    }

    /**
     * A Privacy Doc 'may' have been signed by a company user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedCompany()
    {
        return $this->belongsTo('App\User', 'user_signed_id');
    }

    /**
     * A Privacy Doc is owned by a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A Privacy Doc  was created by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * Create ToDoo for Privacy Doc to be signed and assign to given user(s)
     */
    public function createSignToDo($user_list)
    {
        $company = Company::findOrFail($this->for_company_id);
        $todo_request = [
            'type' => 'company privacy',
            'name' => 'Cope Cod Privacy Policy Sign Off',
            'info' => 'Please read and sign you have read Cape Cod Privacy Policy',
            'due_at' => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
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
     * Close any outstanding ToDoo for this Doc
     */
    public function closeToDo($user = '')
    {
        if (!$user)
            $user = Auth::user();

        $todos = Todo::where('type', 'company privacy')->where('type_id', $this->company->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = $user->id;
            $todo->save();
        }
    }


    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment)
            return '';

        return FileBank::url("company/{$this->company->id}/docs/{$this->attachment}");
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

