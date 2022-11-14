<?php

namespace App\Models\Misc\Form;

use URL;
use Mail;
use App\User;
use App\Models\Comms\Todo;
use App\Models\Site\Site;
//use App\Models\Company\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FormQuestion extends Model {

    protected $table = 'forms_questions';
    protected $fillable = ['template_id', 'page_id', 'section_id', 'name', 'type', 'type_special', 'type_version', 'order', 'default', 'multiple', 'required',
        'placeholder', 'helper', 'width', 'notes', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];


    /*
     * A FormQuestion belongs to a FormSection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo('App\Models\Misc\Form\FormSection', 'section_id');
    }

    /**
     * A FormQuestion has many notes
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function extraNotes()
    {
        return $this->hasMany('App\Models\Misc\Form\FormNote', 'question_id');
    }

    /**
     * A FormQuestion many have notes for a 'certain' form
     */
    public function extraNotesForm($form_id)
    {
        return FormNote::where('form_id', $form_id)->where('question_id', $this->id)->first();
    }

    /**
     * A FormQuestion has many logic
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function logic()
    {
        return $this->hasMany('App\Models\Misc\Form\FormLogic', 'question_id');
    }

    /**
     * A FormQuestion many affected by another questions logic
     */
    public function affectedByLogic()
    {
        $logic_questions = FormLogic::where('trigger', 'question')->where('trigger_id', $this->id)->pluck('id')->toArray();
        $logic_sections = FormLogic::where('trigger', 'section')->where('trigger_id', $this->section->id)->pluck('id')->toArray();
        $logic_ids = array_merge($logic_questions, $logic_sections);

        return FormLogic::find($logic_ids);
    }


    /**
     * A FormQuestion 'may' have many options
     */
    public function options()
    {
        if ($this->type == 'select') {
            if (in_array($this->type_special, ['YN', 'YrN', 'YgN'])) {
                $option_ids = [];
                if ($this->type_special == 'CONN') $option_ids = [1, 2, 3, 4];
                if ($this->type_special == 'YN') $option_ids = [5, 6];
                if ($this->type_special == 'YrN') $option_ids = [7, 8];
                if ($this->type_special == 'YgN') $option_ids = [9, 10];
                if ($this->type_special == 'YNNA') $option_ids = [11, 12, 13];

                return FormOption::find($option_ids)->sortBy('order');
            } else
                return FormOption::where('question_id', $this->id)->where('status', 1)->orderBy('order')->get();
        }

        return null;
    }

    /**
     * A FormQuestion 'may' have many options
     *
     */
    public function optionsArray()
    {
        if ($this->type == 'select')
            $select_placeholder = ($this->multiple) ? ['' => 'Select one or more options'] : ['' => 'Select option'];

        return $this->options()->pluck('text', 'id')->toArray();

        return [];
    }

    /**
     * A FormQuestion 'may' have files for a certain 'form'
     */
    public function files($form_id)
    {
        return FormFile::where('form_id', $form_id)->where('question_id', $this->id)->get();
    }

    /**
     * A FormQuestion 'may' have action for a certain 'form'
     */
    public function actions($form_id, $status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'inspection')->where('type_id', $form_id)->where('type_id2', $this->id)->get();

        return Todo::where('type', 'inspection')->where('type_id', $form_id)->where('type_id2', $this->id)->get();

        //return FormAction::where('form_id', $form_id)->where('question_id', $this->id)->get();
    }

    /**
     * A FormQuestion 'may' have responses for a certain 'form'
     */
    public function response($form_id)
    {
        return FormResponse::where('form_id', $form_id)->where('question_id', $this->id)->get();
    }

    /**
     * A FormQuestion 'may' have responses for a certain 'form'
     */
    public function responseFormatted($form_id)
    {
        $responses = FormResponse::where('form_id', $form_id)->where('question_id', $this->id)->get();
        if (count($responses))
            $values = ($this->multiple) ? $responses->pluck('value')->toArray() : [$responses->first()->value];
        else
            return '';

        if ($this->type_special == 'site') {
            $site = Site::find($values[0]);

            return "$site->name ($site->address, $site->suburb)";
        }

        if ($this->type_special == 'staff') {
            $user = User::find($values[0]);

            return "$user->name";
        }
        // Custom Buttons
        if ($this->type_special && !in_array($this->type_special, ['site', 'staff'])) {
            return customFormSelectButtons($this->id, $values[0], 0);
        }

        if (in_array($this->type, ['text', 'textarea']))
            return $values[0];
        else {
            $str = '';

            foreach ($values as $option_id) {
                $option = FormOption::find($option_id);
                if ($option)
                    $str .= "$option->text<br>";
            }

            return $str;
        }
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