<?php

namespace App\Models\Misc\Form;

use URL;
use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FormAction extends Model {

    protected $table = 'forms_actions';
    protected $fillable = ['form_id', 'question_id', 'todo_id', 'action', 'attachment', 'notes', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];

    /*
     * A FormAction belongs to a FormQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo('App\Models\Misc\Form\FormQuestion', 'question_id');
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