<?php

namespace App\Models\Company;

use App\User;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class CompanyDocReviewFile extends Model
{

    protected $table = 'company_docs_review_files';
    protected $fillable = ['review_id', 'attachment', 'notes', 'status', 'created_by', 'updated_by'];


    /**
     * A CompanyReviewDocFile is for a CompanyDocReview.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function reviewdoc()
    {
        return $this->belongsTo('App\Models\Company\CompanyDocReview', 'review_id');
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
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'])// && file_exists(public_path('/filebank/company/' . $this->company->id . '/docs/' . $this->attributes['attachment'])))
            return '/filebank/company/' . $this->reviewdoc->company_doc->company_id . '/docs/review/' . $this->attributes['attachment'];

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

