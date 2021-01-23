<?php

namespace App\Models\Company;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CompanyDocCategory extends Model {

    protected $table = 'company_docs_categories';
    protected $fillable = [
        'type', 'name', 'parent', 'company_id',
        'status', 'created_by', 'updated_by'];

    /**
     * A Category belongs to a Company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company() {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A Category has many documents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents() {
        return $this->hasMany('App\Models\Company\CompanyDoc', 'category_id');
    }

    /**
     * A Category has many sub-categories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subcategories() {
        return $this->hasMany('App\Models\Company\CompanyDocCategory', 'parent');
    }

    /**
     * A dropdown list of sub-categories for given category.
     *
     * @return array
     */
    public function subcategorySelect($prompt = '')
    {
        $array = [];
        foreach ($this->subcategories as $sub)
            $array[$sub->id] = $sub->name;

        asort($array);

        return ($prompt) ? $array = array('' => 'Select sub-category') + $array : $array;
    }

    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->company;
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
    public static function boot() {
        parent::boot();

        if(Auth::check()) {
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

