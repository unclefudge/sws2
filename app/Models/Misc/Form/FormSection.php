<?php

namespace App\Models\Misc\Form;

use URL;
use Mail;
use App\User;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FormSection extends Model {

    protected $table = 'forms_sections';
    protected $fillable = ['template_id', 'page_id', 'parent', 'name', 'description', 'order', 'notes', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];

    /*
     * A FormSection belongs to a FormPage
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo('App\Models\Misc\Form\FormPage', 'page_id');
    }


    /**
     * A FormSection has many questions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function questions()
    {
        return $this->hasMany('App\Models\Misc\Form\FormQuestion', 'section_id');
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