<?php

namespace App\Models\Misc;

use URL;
use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FormQuestion extends Model {

    protected $table = 'form_questions';
    protected $fillable = ['type', 'name', 'parent', 'order', 'form', 'company_id', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];

    /*
     * A FormQuestion 'options' have have a 'parent' question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return ($this->parent) ? $this->belongsTo('App\Models\Misc\FormQuestion', 'parent') : null;
    }

    /**
     * A FormQuestion 'question' have 'child' options
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function options()
    {
        return (!$this->parent) ? $this->hasMany('App\Models\Misc\FormQuestion', 'parent') : null;
    }

    /**
     * A FormQuestion optioms in array format
     *
     * @return array
     */
    public function optionsArray()
    {
        return (!$this->parent) ? $this->hasMany('App\Models\Misc\FormQuestion', 'parent')->orderBy('order')->pluck('name', 'id')->toArray() : [];
    }

    /**
     * A FormQuestion has many responses
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function responses($table = null, $table_id = null)
    {
        if ($table && $table_id)
            return $this->hasMany('App\Models\Misc\FormResponse', 'question_id')->where('table', $table)->where('table_id', $table_id)->get();

        return $this->hasMany('App\Models\Misc\FormResponse', 'question_id')->get();
    }

    /**
     * A FormQuestion responses in array format 'option_id' only
     *
     * @return array
     */
    public function responsesArray($table = null, $table_id = null)
    {
        return $this->responses($table, $table_id)->pluck('option_id')->toArray();
    }

    /**
     * A FormQuestion responses in array format 'option_id' only
     *
     * @return string
     */
    public function responsesCSV($table = null, $table_id = null)
    {
        $str = '';
        foreach ($this->responses($table, $table_id) as $response)
            $str .= $response->optionText . ', ';

        $str = rtrim($str, ', ');

        return $str;
    }

    /**
     * A FormQuestion response text for specific option
     *
     * @return string
     */
    public function responseText($table = null, $table_id = null, $option_id = null)
    {
        $response = FormResponse::where('table', $table)->where('table_id', $table_id)->where('option_id', $option_id)->first();

        return ($response) ? $response->optionText : '';
    }

    /**
     * A FormQuestion custom response
     *
     * @return boolean
     */
    public function responseOther($table = null, $table_id = null, $option_id = null)
    {
        $response = FormResponse::where('table', $table)->where('table_id', $table_id)->where('option_id', $option_id)->first();

        return ($response && $response->info)  ? $response->info : '';
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