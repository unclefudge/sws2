<?php

namespace App\Models\Company;

use App\Services\FileBank;
use App\User;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class CompanyDocSubcontractorStatement extends Model
{

    protected $table = 'company_doc_ss';
    protected $fillable = [
        'from', 'to', 'attachment', 'contract_no', 'claim_payment', 'clause_a', 'wc_date', 'principle_id', 'principle_name', 'principle_abn',
        'principle_signed_id', 'principle_signed_at', 'principle_signed_name',
        'contractor_id', 'contractor_name', 'contractor_address', 'contractor_abn',
        'contractor_full_name', 'contractor_signed_title', 'contractor_signed_id', 'contractor_signed_at', 'contractor_signed_name',
        'share', 'notes', 'reject', 'for_company_id', 'company_id', 'status', 'created_by', 'updated_by'];

    protected $casts = ['from' => 'datetime', 'to' => 'datetime', 'claim_payment' => 'datetime', 'wc_date' => 'datetime', 'principle_signed_at' => 'datetime', 'contractor_signed_at' => 'datetime'];

    /**
     * A SS Doc is for a specific company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company', 'for_company_id');
    }

    /**
     * A SS Doc 'may' have a Principle company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function principleCompany()
    {
        return $this->belongsTo('App\Models\Company\Company', 'principle_id');
    }

    /**
     * A SS Doc 'may' have been signed by a Principle company user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedPrinciple()
    {
        return $this->belongsTo('App\User', 'principle_signed_id');
    }

    /**
     * A SS Doc 'may' have been signed by a company user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedCompany()
    {
        return $this->belongsTo('App\User', 'user_signed_id');
    }

    /**
     * A SS Doc is owned by a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A SS Doc  was created by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
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

