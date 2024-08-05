<?php

namespace App\Models\Site;

use App\Http\Controllers\CronCrontroller;
use App\Models\Comms\Todo;
use App\Models\Misc\TemporaryFile;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class SiteScaffoldHandover extends Model
{

    protected $table = 'site_scaffold_handover';
    protected $fillable = [
        'site_id', 'location', 'use', 'duty', 'decks', 'inspector_name', 'inspector_licence', 'handover_date',
        'signed_by', 'signed_at', 'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['handover_date' => 'datetime', 'signed_at' => 'datetime'];


    /**
     * A Site ScaffoldHandover belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }


    /**
     * A Site ScaffoldHandover has many Docs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function docs()
    {
        return $this->hasMany('App\Models\Site\SiteScaffoldHandoverDoc', 'scaffold_id');
    }


    /**
     * Email Report
     */
    public function emailReport($user)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = (validEmail($user->email)) ? $user->email : '';
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($this));
        elseif ($email_user)
            Mail::to($email_user)->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($this));

    }

    public function emailReportCreated()
    {
        $email_cc = [];

        if (\App::environment('prod')) {
            $email_to = [];
            if (validEmail($this->site->supervisorEmail))
                $email_to[] = $this->site->supervisorEmail;
            $email_cc = ['ianscottewin@gmail.com', 'kirstie@capecod.com.au', 'michelle@capecod.com.au'];
        } else
            $email_to = [env('EMAIL_DEV')];


        if ($email_to && $email_cc)
            Mail::to($email_to)->cc([$email_cc])->send(new \App\Mail\Site\SiteScaffoldHandoverCreated($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteScaffoldHandoverCreated($this));

    }

    /**
     * Create ToDoo for Report and assign to given user(s)
     *//*
    public function createCertificateToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type'       => 'scaffold handover',
            'type_id'    => $this->site_id,
            'name'       => 'Scaffold Handover Certificate for ' . $site->name,
            'info'       => 'Please complete the Scaffold Handover Certificate for '.$site->name,
            'priority'   => '1',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => '3',
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }*/

    /**
     * Close any outstanding ToDoo for this Certificate
     */
    public function closeToDo()
    {
        $todos = Todo::where('type', 'scaffold handover')->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = Auth::user()->id;
            $todo->save();
        }
    }

    /**
     * Save attached Media to existing Issue
     */
    public function saveAttachment($tmp_filename)
    {
        $tempFile = TemporaryFile::where('folder', $tmp_filename)->first();
        if ($tempFile) {
            // Move temp file to support ticket directory
            $dir = "filebank/site/" . $this->site_id . '/scaffold';
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

            $tempFilePublicPath = public_path($tempFile->folder) . "/" . $tempFile->filename;
            if (file_exists($tempFilePublicPath)) {
                $newFile = $this->site_id . '-' . $tempFile->filename;

                // Ensure filename is unique by adding counter to similiar filenames
                $count = 1;
                while (file_exists(public_path("$dir/$newFile"))) {
                    $ext = pathinfo($newFile, PATHINFO_EXTENSION);
                    $filename = pathinfo($newFile, PATHINFO_FILENAME);
                    $newFile = $filename . $count++ . ".$ext";
                }
                rename($tempFilePublicPath, public_path("$dir/$newFile"));

                // Determine file extension and set type
                $ext = pathinfo($tempFile->filename, PATHINFO_EXTENSION);
                $orig_filename = pathinfo($tempFile->filename, PATHINFO_BASENAME);
                $type = (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'])) ? 'image' : 'file';
                $new = SiteScaffoldHandoverDoc::create(['scaffold_id' => $this->id, 'type' => $type, 'category' => 'setup', 'name' => $orig_filename, 'attachment' => $newFile]);
            }

            // Delete Temporary file directory + record
            $tempFile->delete();
            $files = scandir($tempFile->folder);
            if (count($files) == 0)
                rmdir(public_path($tempFile->folder));
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
     * Get the Licence URL (setter)
     */
    public function getInspectorLicenceUrlAttribute()
    {
        if ($this->attributes['inspector_licence'])
            return '/filebank/site/' . $this->attributes['site_id'] . "/scaffold/" . $this->attributes['inspector_licence'];

        return '';
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

