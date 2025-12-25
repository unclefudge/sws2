<?php

namespace App\Models\Client;

use App\Http\Controllers\CronCrontroller;
use App\Models\Misc\Attachment;
use App\Services\FileBank;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class ClientPlannerEmail extends Model
{

    protected $table = 'client_planner_emails';
    protected $fillable = [
        'client_id', 'site_id', 'type', 'name', 'email1', 'email2', 'intro', 'sent_to', 'sent_cc', 'sent_bcc', 'subject', 'body',
        'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];


    /**
     * A Client Planner Email belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }


    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_id')->where('table', 'client_planner_emails');
    }


    /**
     * Email Planner
     */
    public function emailPlanner()
    {
        $email_to = [env('EMAIL_DEV')];
        $email_cc = '';
        $email_bcc = '';
        $files = [];

        if (app()->environment('prod')) {
            $email_to = explode('; ', $this->sent_to);
            $email_cc = explode('; ', $this->sent_cc);
            //$email_cc = 'support@openhands.com.au';
            $email_bcc = explode('; ', $this->sent_bcc);
        }

        $data = ['client_planner' => $this,];
        $client_planner = $this;
        $files = $this->attachments;


        if ($email_to) {
            Mail::send('emails/client/planner', $data, function ($m) use ($email_to, $email_cc, $data, $client_planner, $files) {
                //$send_from = 'do-not-reply@safeworksite.com.au';
                $send_from = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_to);
                if ($email_cc)
                    $m->cc($email_cc);
                $m->subject($client_planner->subject);
                if ($files->count()) {
                    foreach ($files as $file) {
                        if (!$file->attachment) continue;
                        FileBank::attachToEmail($m, "$file->directory/$file->attachment");
                    }
                }
            });
        }
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
     * Get the Last Email (setter)
     */
    public function getLastEmailAttribute()
    {
        $last_email = ClientPlannerEmail::where('status', 0)->where('site_id', $this->site_id)->orderBy('updated_at', 'DESC')->first();

        if ($last_email)
            return $last_email->updated_at->format('d/m/Y');

        return '';
    }

    /**
     * Get the Licence URL (setter)
     */
    public function getSentByAttribute()
    {
        return User::findOrFail($this->updated_by);
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

