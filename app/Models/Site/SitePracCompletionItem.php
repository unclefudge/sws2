<?php

namespace App\Models\Site;

use App\Models\Comms\Todo;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;

class SitePracCompletionItem extends Model
{

    protected $table = 'site_prac_completion_items';
    protected $fillable = [
        'prac_id', 'name', 'assigned_to', 'planner_id', 'order', 'status',
        'sign_by', 'sign_at', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['sign_at' => 'datetime'];

    public function prac()
    {
        return $this->belongsTo('App\Models\Site\SitePracCompletion', 'prac_id');
    }

    public function planner()
    {
        return $this->belongsTo('App\Models\Site\Planner\SitePlanner', 'planner_id');
    }

    public function assigned()
    {
        return $this->belongsTo('App\Models\Company\Company', 'assigned_to');
    }

    public function todos($status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'prac_completion_item')->where('type_id', $this->id)->get();

        return Todo::where('type', 'prac_completion_item')->where('type_id', $this->id)->get();
    }

    public function closeToDo($type = 'prac_completion_item')
    {
        $todos = Todo::where('type', $type)->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = Auth::user()->id;
            $todo->save();
        }
    }

    public function createAssignSupervisorToDo($user_list)
    {
        // Create ToDoo for assignment to Supervisor
        $todo_request = [
            'type' => 'prac_completion_item',
            'type_id' => $this->id,
            'name' => 'Prac Completion Item Added - ' . $this->prac->site->name,
            'info' => 'Please review practical completion item and assign to company',
            'due_at' => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->prac->site->owned_by->id,
        ];

        $todo = Todo::create($todo_request);
        //$todo->assignUsers($user_list);
        //$todo->emailToDo('ASSIGNED', ['kirstie@capecod.com.au', 'veronica@capecode.com.au']);
    }


    /**
     * Email Assigned
     */
    public function emailAssigned($user)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = (validEmail($user->email)) ? $user->email : '';
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        //Mail::to($email_to)->send(new \App\Mail\Site\SitePracCompletionAssignedItem($this));

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

