<?php

namespace App\Models\Misc\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class FormPage extends Model
{

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
        return $this->hasMany('App\Models\Misc\Form\FormSection', 'page_id')->where('status', 1);
    }


    public function sections2()
    {
        return $this->hasMany('App\Models\Misc\Form\FormSection', 'page_id')->where('status', 1); //->whereNull('parent');
    }

    public function allSections2()
    {
        return $this->sections2()->with('allSections2')->where('status', 1);
    }


    public function childSections()
    {
        return $this->hasMany('App\Models\Misc\Form\FormSection', 'parent')->where('status', 1);
    }

    /*
     * Recursive Relationship of All Child Sections
     */
    public function allChildSections()
    {
        return $this->childSections()->with('allChildSections')->where('status', 1);
    }

    /**
     * A FormPage has many questions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function questions()
    {
        return $this->hasManyThrough('App\Models\Misc\Form\FormQuestion', 'App\Models\Misc\Form\FormSection', 'page_id', 'section_id', 'id', 'id')->where('status', 1);
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
        } else {
            // create a event to happen on creating
            static::creating(function ($table) {
                $table->created_by = 1;
                $table->updated_by = 1;
            });
        }
    }
}