<?php

namespace App\Models\Misc\Form;

use URL;
use Mail;
use App\User;
use App\Models\Misc\Form\FormTemplate;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormSection;
use App\Models\Misc\Form\FormQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Form extends Model {

    protected $table = 'forms';
    protected $fillable = ['template_id', 'name',  'notes', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];

    /*
     * A Form belongs to a FormTemplate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo('App\Models\Misc\Form\FormTemplate', 'template_id');
    }

    /**
     * A Form has many responses
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function responses()
    {
        return $this->hasMany('App\Models\Misc\Form\FormResponse',  'form_id',);
    }


    /**
     * Form pages
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function pages()
    {
        return $this->hasManyThrough('App\Models\Misc\Form\FormPage', 'App\Models\Misc\Form\FormTemplate', 'id', 'template_id', 'id', 'id');
        //return FormPage::where('template_id', $this->template_id)->where('status', 1)->orderBy('order')->get();
    }

    /**
     * Form Sections
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function sections()
    {
        return $this->hasManyThrough('App\Models\Misc\Form\FormSection', 'App\Models\Misc\Form\FormPage', 'template_id', 'page_id', 'id', 'id');
        //return FormSection::whereIn('page_id', $this->pages()->pluck('id')->toArray())->where('status', 1)->orderBy('order')->get();
    }

    /**
     * A Form has many questions
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