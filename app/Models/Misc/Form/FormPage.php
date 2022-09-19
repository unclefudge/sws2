<?php

namespace App\Models\Misc\Form;

use URL;
use Mail;
use App\User;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormSection;
use App\Models\Misc\Form\FormQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FormPage extends Model {

    protected $table = 'forms_pages';
    protected $fillable = ['template_id', 'name', 'description', 'order', 'notes', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];


    /*
     * A FormPage belongs to a FormTemplate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo('App\Models\Misc\Form\FormTemplate', 'template_id');
    }


    /**
     * A FormPage has many sections
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function sections()
    {
        return $this->hasMany('App\Models\Misc\Form\FormSection', 'page_id');
    }

    /**
     * A FormPage has many questions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function questions()
    {
        return $this->hasManyThrough('App\Models\Misc\Form\FormQuestion', 'App\Models\Misc\Form\FormSection', 'page_id', 'section_id', 'id', 'id');
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