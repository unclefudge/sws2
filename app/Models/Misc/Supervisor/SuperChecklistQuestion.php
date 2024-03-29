<?php

namespace App\Models\Misc\Supervisor;

use App\Models\Site\Site;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Mail;
use URL;

class SuperChecklistQuestion extends Model
{

    protected $table = 'supervisor_checklist_questions';
    protected $fillable = ['cat_id', 'name', 'type', 'order', 'default', 'multiple', 'required', 'placeholder',
        'notes', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];

    /*
    * A SuperChecklistQuestion belongs to a SuperChecklistCategory
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function category()
    {
        return $this->belongsTo('App\Models\Misc\Supervisor\SuperChecklistCategory', 'cat_id');
    }

    /**
     * A SuperChecklistQuestion 'may' have many options
     */
    public function options()
    {
        if ($this->type == 'select') {
            if (in_array($this->type_special, ['YN', 'YrN', 'YgN', 'YgNr'])) {
                $option_ids = [];
                if ($this->type_special == 'CONN') $option_ids = [1, 2, 3, 4];
                if ($this->type_special == 'YN') $option_ids = [5, 6];
                if ($this->type_special == 'YrN') $option_ids = [7, 8];
                if ($this->type_special == 'YgN') $option_ids = [9, 10];
                if ($this->type_special == 'YNNA') $option_ids = [11, 12, 13];
                if ($this->type_special == 'YgNr') $option_ids = [14, 15];

                return FormOption::find($option_ids)->sortBy('order');
            } else
                return FormOption::where('question_id', $this->id)->where('status', 1)->orderBy('order')->get();
        }

        return [];
    }

    /**
     * A SuperChecklistQuestion 'may' have many options
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
     * A SuperChecklistQuestion 'may' have responses
     */
    public function response($checklist_id)
    {
        return SuperChecklistResponse::where('checklist_id', $checklist_id)->where('question_id', $this->id)->get();
    }

    /**
     * A SuperChecklistQuestion 'may' have responses
     */
    public function responseFormatted($checklist_id)
    {
        $responses = SuperChecklistResponse::where('form_id', $checklist_id)->where('question_id', $this->id)->get();
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
        if ($this->type_special && !in_array($this->type_special, ['site', 'staff'])) { //i ie YN, YrN, YgN, YgNr, button, CONN
            return customFormSelectButtons($this->id, $values[0], 0);
        }

        // Datetime
        if ($this->type == 'datetime') {
            $response = FormResponse::where('form_id', $checklist_id)->where('question_id', $this->id)->first();
            return ($response->date) ? $response->date->format('d/m/Y g:i a') : $response->value;
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

    public function isRequiredForSupervisor($super, $day)
    {
        $today = Carbon::now();
        if ($this->required) {
            // Only add New Project question for Supers with active jobs with future JobStart
            if ($this->cat_id == '4') {
                $sites = Site::where('supervisor_id', $super->id)->where('status', 1)->get();
                foreach ($sites as $site) {
                    if ($site->JobStart && $site->JobStart->gt($today))
                        return true;  // Upcoming New Project
                }
                return false; // No upcoming project
            }

            // Check if question only required on certain days
            if ($this->days) {
                $days = explode(',', $this->days);
                return in_array($day, $days);
            }

            return true;  // Required for all days
        }

        return false;  // Not required
    }
}