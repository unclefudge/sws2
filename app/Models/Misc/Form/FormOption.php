<?php

namespace App\Models\Misc\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class FormOption extends Model
{

    protected $table = 'forms_options';
    protected $fillable = ['question_id', 'text', 'value', 'order', 'colour', 'score', 'group', 'master', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];

    /*
     * A FormOption 'may' belong to a FormQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

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

    public function question()
    {
        if ($this->master)
            return $this->belongsTo('App\Models\Misc\Form\FormQuestion', 'question_id');

        return null;
    }
}