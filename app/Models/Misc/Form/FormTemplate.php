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

class FormTemplate extends Model {

    protected $table = 'form_templates';
    protected $fillable = ['name', 'description', 'status', 'company_id', 'created_by', 'created_at', 'updated_at', 'updated_by'];

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
        //return $this->hasMany('App\Models\Misc\Form\FormSection', 'template_id');
    }

    /**
     * A FormTemplate has many questions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function questions()
    {
        return $this->hasManyThrough('App\Models\Misc\Form\FormQuestion', 'App\Models\Misc\Form\FormSection', 'page_id', 'section_id', 'id', 'id');
        //return $this->hasMany('App\Models\Misc\Form\FormQuestion', 'template_id');
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