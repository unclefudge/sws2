<?php

namespace App\Models\Misc\Form;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class FormTemplate extends Model
{

    protected $table = 'forms_templates';
    protected $fillable = ['parent_id', 'current_id', 'name', 'description', 'version', 'notes', 'status', 'company_id', 'created_by', 'created_at', 'updated_at', 'updated_by'];


    /**
     * A FormTemplate has many forms
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function forms()
    {
        return $this->hasMany('App\Models\Misc\Form\Form', 'template_id');
    }

    /**
     * A FormTemplate has many pages
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function pages()
    {
        return $this->hasMany('App\Models\Misc\Form\FormPage', 'template_id');
    }

    /**
     * A FormTemplate has many sections
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function sections()
    {
        return $this->hasManyThrough('App\Models\Misc\Form\FormSection', 'App\Models\Misc\Form\FormPage', 'template_id', 'page_id', 'id', 'id');
    }

    /**
     * A FormTemplate has many questions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function questions()
    {
        //$sections_array = $this->sections->pluck('id')->toArray();
        //return FormQuestion::whereIn('section_id',$sections_array)->where('status', 1)->get();
        //return $this->hasManyThrough('App\Models\Misc\Form\FormQuestion', 'App\Models\Misc\Form\FormSection', 'page_id', 'section_id', 'id', 'id');
        return $this->hasMany('App\Models\Misc\Form\FormQuestion', 'template_id');
    }

    /**
     * A FormTemplate has many logic
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function logic()
    {
        return $this->hasMany('App\Models\Misc\Form\FormLogic', 'template_id');
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
        } else {
            // create a event to happen on creating
            static::creating(function ($table) {
                $table->created_by = 1;
                $table->updated_by = 1;
            });
        }
    }
}