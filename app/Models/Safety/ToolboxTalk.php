<?php

namespace App\Models\Safety;

use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class ToolboxTalk extends Model
{

    protected $table = 'toolbox_talks';
    protected $fillable = [
        'name', 'overview', 'hazards', 'controls', 'further', 'version', 'master', 'master_id',
        'authorised_by', 'authorised_at', 'review_at',
        'for_company_id', 'company_id', 'share', 'status', 'created_by', 'updated_by'];

    protected $casts = ['authorised_at' => 'datetime', 'review_at' => 'datetime'];


    /**
     * A ToolboxTalk is owned by a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A ToolboxTalk is for a specific company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company', 'for_company_id');
    }

    /**
     * A Talkbox Talk was created by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A Talkbox Talk 'may' have been signed by a Authorised user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function authorisedBy()
    {
        return $this->belongsTo('App\User', 'authorised_by');
    }

    /**
     * Toolbox Talk URL
     *
     * @return string
     */
    public function url()
    {
        return '/safety/doc/toolbox2/' . $this->id;
    }

    /**
     * Toolbox Talk Upload Files
     *
     * @return string
     */
    public function uploadedFilesURL()
    {
        $files = [];
        if (file_exists(public_path('/filebank/whs/toolbox/' . $this->id))) {
            $scan = scandir(public_path('/filebank/whs/toolbox/') . $this->id);
            foreach ($scan as $file)
                if (!preg_match('/^\./', $file))
                    $files[] = URL::to('/') . '/filebank/whs/toolbox/' . $this->id . '/' . $file;
        }

        return $files;
    }

    /**
     * Mark a Toolbox Talk opened by given user
     */
    public function markOpened($user)
    {
        // Current ToDos for toolbox
        $todo_ids = Todo::where('type', 'toolbox')->where('type_id', $this->id)->pluck('id')->toArray();
        if ($todo_ids) {
            // Get specific Todoo for given user
            $todo_user = TodoUser::whereIn('todo_id', $todo_ids)->where('user_id', $user->id)->where('opened', 0)->first();
            if ($todo_user) {
                $todo_user->opened = 1;
                if ($todo_user->opened_at == null)
                    $todo_user->opened_at = Carbon::now();
                $todo_user->save();
            }
        }
    }

    /**
     * Mark a Toolbox Talk acknowledged by given user
     */
    public function markAccepted($user)
    {
        // Current ToDos for toolbox
        $todo_ids = Todo::where('type', 'toolbox')->where('type_id', $this->id)->pluck('id')->toArray();
        if ($todo_ids) {
            // Get specific Todoo for given user
            $todo_user = TodoUser::whereIn('todo_id', $todo_ids)->where('user_id', $user->id)->first();
            if ($todo_user) {
                $todo = Todo::find($todo_user->todo_id);
                $todo->status = 0;
                $todo->done_at = Carbon::now();
                $todo->done_by = $user->id;
                $todo->save();
            }
        }
    }

    /**
     * A Talkbox Talk 'may' have been assigned to multiple users
     */
    public function assignedTo()
    {
        $todo_ids = Todo::where('type', 'toolbox')->where('type_id', $this->id)->pluck('id')->toArray();
        if ($todo_ids) {
            $users_ids = TodoUser::whereIn('todo_id', $todo_ids)->pluck('user_id')->toArray();

            return User::whereIn('id', $users_ids)->get();
        }

        return null;
    }

    /**
     * A Talkbox Talk assigned to - return list separated by comma
     *
     * return string
     */
    public function assignedToSBC()
    {
        $string = '';
        foreach ($this->assignedTo() as $u) {
            $todo = Todo::where('type', 'toolbox')->where('type_id', $this->id)->where('done_by', $u->id)->first();
            $string .= $u->fullname . ', ';
        }
        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * Determine if a Talkbox Talk has been assigned to a specific users
     */
    public function isAssignedToUser($user)
    {
        $todo_ids = Todo::where('type', 'toolbox')->where('type_id', $this->id)->pluck('id')->toArray();
        if ($todo_ids) {
            $user_ids = TodoUser::whereIn('todo_id', $todo_ids)->pluck('user_id')->toArray();
            if (in_array($user->id, $user_ids))
                return true;
        }

        return false;
    }

    /**
     * Determine if a user is required to read the Toolbox Talk
     * Toolbox has to be open + non-template
     */
    public function userRequiredToRead($user)
    {
        if (!$this->master && $this->status == 1 && $this->assignedTo() && $this->assignedTo()->contains('id', $user->id))
            return $this->outstandingBy()->contains('id', $user->id);

        return false;
    }

    /**
     * Determine if a user has read 'completed' the Toolbox Talk and return date
     */
    public function userCompleted($user)
    {
        $todo = Todo::where('type', 'toolbox')->where('type_id', $this->id)->where('status', 0)->where('done_by', $user->id)->first();
        if ($todo)
            return $todo->done_at;

        return false;
    }

    /**
     * A Talkbox Talk 'may' have been completed by multiple users
     */
    public function completedBy()
    {
        $user_ids = Todo::where('type', 'toolbox')->where('type_id', $this->id)->where('status', 0)->pluck('done_by')->toArray();

        return User::whereIn('id', $user_ids)->orderBy('firstname')->get();
    }

    /**
     * A Talkbox Talk 'may' have been completed by multiple users - return list separated by comma
     *
     * return string
     */
    public function completedBySBC()
    {
        $string = '';
        foreach ($this->completedBy() as $u) {
            $todo = Todo::where('type', 'toolbox')->where('type_id', $this->id)->where('done_by', $u->id)->first();
            $string .= $u->fullname . ' (' . $todo->done_at->format('j/n/y') . '), ';
        }
        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * A Talkbox Talk 'may' be outstanding by multiple users
     */
    public function outstandingBy()
    {
        $outstanding = Todo::where('type', 'toolbox')->where('type_id', $this->id)->where('done_by', 0)->pluck('id')->toArray();
        $user_ids = TodoUser::whereIn('todo_id', $outstanding)->pluck('user_id')->toArray();

        return User::whereIn('id', $user_ids)->orderBy('firstname')->get();
    }

    /**
     * A Talkbox Talk 'may' be outstanding by multiple users - return list separated by comma
     */
    public function outstandingBySBC()
    {
        $string = '';
        foreach ($this->outstandingBy() as $u)
            $string .= $u->fullname . ', ';
        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * Output foield with iframe
     */
    public function iframeField($field)
    {
        $content = $this->$field;

        preg_match_all('/<iframe(.*?)<\/iframe>/s', $content, $matches);

        $youtube_url = ['www.youtube.com', 'youtube.com'];

        $replace = [];
        if ($matches[0] && $matches[1]) {
            //dd($matches);
            foreach ($matches[0] as $match) {
                $placeholder = URL::to('/') . '/img/video-placeholder.png';
                list($crap, $src1) = explode('src="', $match);
                list ($src, $crap2) = explode('"', $src1);
                if (!(str_starts_with($src, 'http:') || str_starts_with($src, 'https:'))) {
                    $src = "http:$src";
                    $replace[] = ['code' => $match, 'src' => $src, 'youtube' => 'n', 'placeholder' => $placeholder];
                } else {
                    $url = parse_url($src);
                    $youtube = (in_array($url['host'], $youtube_url)) ? 'y' : 'n';
                    if ($youtube == 'y') {
                        list($crap, $vid) = explode('v=', $url['query'], 2);
                        $placeholder = "http://img.youtube.com/vi/$vid/0.jpg";
                    }
                    $replace[] = ['code' => $match, 'src' => $src, 'youtube' => $youtube, 'placeholder' => $placeholder];

                    //https://www.youtube.com/watch?v=XP1yIXHfswc
                }

                //echo "src: $src<br>";

            }
            //dd($replace);

            if (count($replace)) {
                foreach ($replace as $rep) {
                    //echo "code: ".$rep['code']."<br>";
                    //dd("src: ".$rep['src']."<br>");
                    $link = $rep['src']; // <img src='".$rep['placeholder']."' style='max-width: 100%'>
                    $url = "background-image: url(&#39;" . $rep['placeholder'] . "&#39;)";
                    if ($rep['youtube'] == 'y') {
                        $content = str_replace($rep['code'], "<div style='" . $url . "; background-repeat: no-repeat; background-size: auto';><a href='$link'><img src='" . URL::to('/') . "/img/youtube-play-transparent.png' style='width: 400px'></a>", $content);
                    } else {
                        $content = str_replace($rep['code'], "<a href='$link'><img src='" . URL::to('/') . "/img/video-placeholder.png' style='width: 460px;'></a>", $content);
                    }
                    //$youtube_play = ($rep['youtube'] == 'y') ? "<img src='/img/youtube-play-transparent.png' style='max-widthh: 100%'>" : "<img src='/img/youtube-play-transparent.png' style='max-widthh: 100%; opacity:0'>";
                    //$content = preg_replace('')
                    //dd($url);
                    //$content = str_replace($rep['code'], "<div style='".$url."; background-repeat: no-repeat; background-size: auto'; width: 600px><a href='$link'>$youtube_play</a>", $content);
                    //$content = str_replace("url(", "url('", $content);
                    //$content = str_replace(".jpg)", ".jpg')", $content);
                }
            }
        }

        return $content;
    }


    /**
     * Email talk to someone for Sign Off
     */
    /*
    public function emailSignOff()
    {
        $email_to = [];
        if (\App::environment('dev', 'prod'))
            $email_to[] = $this->owned_by->notificationsUsersEmailType('doc.whs.approval');   // WHS Mgr
        else
            $email_to[] = env('EMAIL_ME');
        $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        //$email_user = '';

        $data = [
            'user_email'        => Auth::user()->email,
            'user_fullname'     => Auth::user()->fullname,
            'user_company_name' => Auth::user()->company->name,
            'talk_name'         => $this->name,
        ];
        $talk = $this;
        Mail::send('emails/toolbox-signoff', $data, function ($m) use ($email_to, $email_user, $talk, $data) {
            ($email_user) ? $send_from = $email_user : $send_from = 'do-not-reply@safeworksite.com.au';
            $m->from($send_from, Auth::user()->fullname);
            $m->to($email_to);
            if ($email_user)
                $m->cc($email_user);
            $m->subject('Toolbox Talk Sign Off Request - ' . $talk->name);
        });
    }*/

    /**
     * Email talk as Rejected
     */
    /*
    public function emailReject()
    {
        $email_to = [];
        if (\App::environment('dev', 'prod'))
            $email_to[] = $this->owned_by->notificationsUsersEmailType('doc.whs.approval');   // WHS Mgr
        else
            $email_to[] = env('EMAIL_ME');
        // Send to User who created
        if (validEmail($this->createdBy->email))
            $email_to[] = $this->createdBy->email;
        $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';

        $data = [
            'user_email'        => Auth::user()->email,
            'user_fullname'     => Auth::user()->fullname,
            'user_company_name' => Auth::user()->company->name,
            'talk_name'         => $this->name,
        ];
        $talk = $this;
        Mail::send('emails/toolbox-rejected', $data, function ($m) use ($email_to, $email_user, $talk, $data) {
            ($email_user) ? $send_from = $email_user : $send_from = 'do-not-reply@safeworksite.com.au';
            $m->from($send_from, Auth::user()->fullname);
            $m->to($email_to);
            if ($email_user)
                $m->cc($email_user);
            $m->subject('Toolbox Talk Sign Off Request Rejected - ' . $talk->name);
        });
    }*/

    /**
     * Email Overdue
     */
    public function emailOverdue()
    {
        $email_to = [env('EMAIL_ME')];
        if (\App::environment('prod')) {
            //$email_to = $this->owned_by->notificationsUsersEmailType('doc.whs.approval');   // WHS Mgr
            $email_to = ['tara@capecod.com.au']; // only to Tara
            // Send to User who created
            if ($this->createdBy && validEmail($this->createdBy->email))
                $email_to[] = $this->createdBy->email;
        }

        $data = [
            'talk_id' => $this->id,
            'talk_name' => $this->name,
            'talk_count' => $this->completedBy()->count() . '/' . $this->assignedTo()->count(),
            'talk_outstanding' => $this->outstandingBySBC(),
            'talk_url' => URL::to('/') . $this->url(),
            'user_fullname' => $this->createdBy->fullname,
            'user_company_name' => $this->createdBy->company->name,
        ];

        if ($email_to) {
            Mail::send('emails/toolbox-overdue', $data, function ($m) use ($email_to) {
                $m->from('do-not-reply@safeworksite.com.au');
                $m->to($email_to);
                $m->subject('Toolbox Talk Overdue Notification');
            });
        }
    }

    /**
     * Email talk to notify it has modified a template
     */
    public function emailModifiedTemplate($diffs)
    {
        $email_to[] = (\App::environment('dev', 'prod')) ? $this->owned_by->notificationsUsersEmailType('doc.whs.approval') : env('EMAIL_ME');
        if ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Safety\ToolboxTalkModifiedTemplate($this, $diffs));

    }

    /**
     * Email talk to notify it has modified a template
     */
    public function emailActiveTemplate()
    {
        $email_to[] = (\App::environment('dev', 'prod')) ? $this->owned_by->notificationsUsersEmailType('doc.whs.approval') : env('EMAIL_ME');
        $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Safety\ToolboxTalkActiveTemplate($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Safety\ToolboxTalkActiveTemplate($this));


    }

    /**
     * Email document to someone
     */
    public function emailArchived()
    {

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