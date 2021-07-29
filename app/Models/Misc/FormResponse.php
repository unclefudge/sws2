<?php

namespace App\Models\Misc;

use URL;
use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FormResponse extends Model {

    protected $table = 'form_responses';
    protected $fillable = ['question_id', 'option_id', 'table', 'table_id', 'info', 'created_by', 'created_at', 'updated_at', 'updated_by'];

    /*
     * A FormResponse belongs to a FormQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo('App\Models\Misc\FormQuestion', 'question_id');
    }

    /**
     * A FormResponse belongs to a FormOption
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function option()
    {
        return $this->belongsTo('App\Models\Misc\FormQuestion', 'option_id');
    }


    /**
     *  Option text (getter)
     */
    public function getOptionTextAttribute()
    {
        return ($this->info) ? $this->info : $this->option->name;
    }

    /**
     * A FormResponse is one off multiple answers to a specific FormQuestion
     */
    public function responses($table = null, $table_id = null)
    {
        if ($table && $table_id)
            return FormResponse::where('question_id')->where('table', $table)->where('table_id', $table_id)->get();

        return null;
    }
}