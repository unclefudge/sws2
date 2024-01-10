<?php

namespace App\Models\Misc\Supervisor;

use URL;
use Mail;
use App\User;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Supervisor\SuperChecklistCategory;
use App\Models\Misc\Supervisor\SuperChecklistQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SuperChecklistResponse extends Model
{

    protected $table = 'supervisor_checklist_responses';
    protected $fillable = ['checklist_id', 'day', 'question_id', 'value', 'date', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];
    protected $casts = ['date' => 'datetime'];


    /*
     * A SuperChecklistResponse belongs to a SuperChecklist
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function checklist()
    {
        return $this->belongsTo('App\Models\Misc\Supervisor\SuperChecklist', 'checklist_id');
    }

    /*
     * A SuperChecklistResponse belongs to a SuperChecklistQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo('App\Models\Misc\Supervisor\SuperChecklistQuestion', 'question_id');
    }

    /**
     * Get the DayOfWeek Alias  (getter)
     */
    public function getButtonAttribute()
    {
        if ($this->value) {
            $button_text = ['y' => 'Yes', 'n' => 'No', 'na' => 'N/A'];
            $button_colours = ['y' => 'green', 'n' => 'red', 'na' => 'dark'];

            return '<button class="btn button-resp ' . $button_colours[$this->value] . '" style="width:50px">' . $button_text[$this->value] . '</button>';
        }

        return '<i class="fa fa-2x fa-ban font-grey" style="margin-top:10px"></i>';
    }

    /**
     * Get the DayOfWeek Alias  (getter)
     */
    public function getButtonValAttribute()
    {
        $button_values = ['Yes' => 'y', 'No' => 'n', 'N/A' => 'na'];

        return ($this->value) ? $button_values[$this->value] : '';
    }

    /**
     * Get the DayOfWeek Alias  (getter)
     */
    public function getDayOfWeekAttribute()
    {
        $days = ['1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri'];

        return $days[$this->day];
    }

    /**
     * Get the Response Date Alias  (getter)
     */
    public function getResponseDateAttribute()
    {
        $date = Carbon::createFromDate($this->checklist->date);

        return $date->addDays($this->day - 1);
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