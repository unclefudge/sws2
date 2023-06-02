<?php

namespace App\Models\Misc\Supervisor;

use URL;
use Mail;
use App\User;
use App\Models\Comms\Todo;
use App\Models\Site\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SuperChecklist extends Model {

    protected $table = 'supervisor_checklist';
    protected $fillable = ['name', 'super_id', 'date',
        'supervisor_sign_by', 'supervisor_sign_at', 'manager_sign_by', 'manager_sign_at', 'approved_by', 'approved_at',
        'attachment', 'notes', 'status', 'created_at', 'updated_at'];
    protected $dates = ['date', 'supervisor_sign_at', 'manager_sign_at', 'approved_at'];

    /*
    * A SuperChecklist belongs to a Supervisor
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function supervisor()
    {
        return $this->belongsTo('App\User', 'super_id');
    }

    /**
     * A SuperChecklist has many responses
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function responses()
    {
        return $this->hasMany('App\Models\Misc\Supervisor\SuperChecklistResponse', 'checklist_id');
    }

    /**
     * A SuperChecklist has many categories
     */
    public function categories()
    {
        return SuperChecklistCategory::where('status', 1)->orderBy('order')->get();
    }

    /**
     * A SuperChecklist has many questions
     */
    public function questions()
    {
        return SuperChecklistQuestion::where('status', 1)->orderBy('order')->get();
    }

    /**
     * Responses Completed
     */
    public function responsesCompleted($day)
    {
        $completed = [];
        foreach ($this->responses->where('day', $day) as $response) {
            if ($response->value)
                $completed[] = $response->id;
        }

        return SuperChecklistResponse::find($completed);
    }

    /**
     * Responses Completed
     */
    public function dayIcon($day)
    {
        $total = $this->responses->where('day', $day)->count();
        $completed = $this->responsesCompleted($day)->count();

        if ($total == $completed)
            return '<i class="fa fa-2x fa-check-circle font-green" id="d-' . $this->id . '-' . $day . '">';
        elseif ($completed)
            return '<i class="fa fa-2x fa-adjust" id="d-' . $this->id . '-' . $day . '">';
        else
            return '<i class="fa fa-2x fa-circle-o font-grey" id="d-' . $this->id . '-' . $day . '">';
    }

    /**
     * Days Completed
     */
    public function daysCompleted()
    {
        $count = 0;
        $total = $this->responses->where('day', 1)->count();
        for ($day = 1; $day < 6; $day ++) {
            $completed = $this->responsesCompleted($day)->count();
            if ($completed == $total)
                $count ++;
        }

        return $count;
    }

    /**
     * Days Half Completed
     */
    public function daysHalfCompleted()
    {
        $count = 0;
        $total = $this->responses->where('day', 1)->count();
        for ($day = 1; $day < 6; $day ++) {
            $completed = $this->responsesCompleted($day)->count();
            if ($completed && $completed != $total)
                $count ++;
        }

        return $count;
    }

    /**
     * Weekly Summary
     */
    public function weeklySummary()
    {
        $total_responses = $this->responses->where('day', 1)->count();
        $summary = '';
        $days_completed = 0;
        for ($day = 1; $day < 6; $day ++) {
            $completed = $this->responsesCompleted($day)->count();
            if ($completed == $total_responses) {
                $days_completed ++;
                $summary .= '<i class="fa fa-star font-green"></i>';
            } elseif ($completed)
                $summary .= '<i class="fa fa-star-half-o"></i>';
            else
                $summary .= '<i class="fa fa-star-o font-red"></i>';
        }

        if ($days_completed == 5)
            $summary = '<i class="fa fa-star font-yellow-saffron"></i>';

        if ($this->supervisor_sign_by && $this->manager_sign_by)
            $summary .= '<i class="fa fa-check-circle font-green"></i>';
        elseif ($this->supervisor_sign_by)
            $summary .= '<i class="fa fa-check-circle"></i>';
        else
            $summary .= '<i class="fa fa-times-circle font-red"></i>';

        return $summary;
    }


    /**
     * Create ToDoo for Super Checklist and assign to given user(s)
     */
    public function createSignOffToDo($user_list)
    {
        $todo_request = [
            'type'       => 'super checklist signoff',
            'type_id'    => $this->id,
            'name'       => 'Weekly Supervisor Checklist - ' . $this->supervisor->name,
            'info'       => 'Please sign off on completed items',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => '3',
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Close any outstanding ToDoo for this Project Supply
     */
    public function closeToDo()
    {
        $todos = Todo::where('type', 'super checklist')->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = (Auth::check()) ? Auth::user()->id : 1;
            $todo->save();
        }
    }

    /**
     * Email Action Notification
     */
    public function emailAction($action, $important = false)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        /*
        if (\App::environment('prod')) {
            $email_list = $this->site->company->notificationsUsersEmailType('site.hazard');
            $email_supers = $this->site->supervisorsEmails();
            $email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteHazardAction($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteHazardAction($this, $action));
        */
    }

    /**
     * Get the SignedByField Alias  (getter)
     */
    public function getSignedByFieldAttribute()
    {
        $str = '';
        if ($this->supervisor_sign_by) {
            $str = "Supervisor: " . $this->supervisor_sign_at->format('d/m/Y') . "<br>";

            if ($this->manager_sign_by)
                $str .= "Manager: " . $this->manager_sign_at->format('d/m/Y') . "<br>";
        } else {
            $str = '-';
        }

        return $str;
    }
}