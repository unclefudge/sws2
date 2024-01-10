<?php

namespace App\Models\Misc\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class FormResponse extends Model
{

    protected $table = 'forms_responses';
    protected $fillable = ['form_id', 'question_id', 'option_id', 'value', 'date', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];
    protected $casts = ['date' => 'datetime'];


    /*
     * A FormResponse belongs to a Form
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo('App\Models\Misc\Form\Form', 'form_id');
    }

    /*
     * A FormResponse belongs to a FormQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo('App\Models\Misc\Form\FormQuestion', 'question_id');
    }

    /*
     * A FormResponse belongs to a FormOption
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function option()
    {
        return $this->belongsTo('App\Models\Misc\Form\FormOption', 'option_id');
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