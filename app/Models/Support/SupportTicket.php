<?php

namespace App\Models\Support;

use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use nilsenj\Toastr\Facades\Toastr;

class SupportTicket extends Model {

    protected $table = 'support_tickets';
    protected $fillable = [
        'name', 'summary', 'type', 'priority', 'eta', 'hours', 'attachment', 'assigned_to', 'notes', 'status', 'resolved_at', 'company_id',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected $dates = ['eta', 'resolved_at'];

    /**
     * A Support Ticket belongs to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }

    /**
     * A Support Ticket belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A Support Ticket assigned to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function assigned()
    {
        return $this->belongsTo('App\User', 'assigned_to');
    }

    /**
     * A Support Ticket has many SiteHazardActions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany('App\Models\Support\SupportTicketAction', 'ticket_id');
    }

    /**
     * Save attachment to existing Issue
     */
    public function saveAttachment($tmp_filename)
    {
        $tempFile = TemporaryFile::where('folder', $tmp_filename)->first();
        if ($tempFile) {
            // Move temp file to support ticket directory
            $dir = "/filebank/support/ticket";
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

            $tempFilePublicPath = public_path($tempFile->folder) . "/" . $tempFile->filename;
            if (file_exists($tempFilePublicPath)) {
                $newFile = "$dir/ticket-" . $ticket->id . '-' . $action->id . '-' . $tempFile->filename;
                rename($tempFilePublicPath, public_path($newFile));
                $form_file = FormFile::create(['form_id' => $form->id, 'question_id' => $question->id, 'type' => 'photo', 'attachment' => $newFile]);
                $response = FormResponse::where('form_id', $form->id)->where('question_id', $qid)->where('value', $form_file)->first();
                if (!$response)
                    $response = FormResponse::create(['form_id' => $form->id, 'question_id' => $qid, 'value' => $form_file->id, 'option_id' => null, 'date' => null]);
                $responses_given[] = $response->id;
            }

            // Delete Temporary file directory + record
            $tempFile->delete();
            rmdir(public_path($tempFile->folder));
        }





        $path = "filebank/support/ticket/";
        $name = 'ticket-' . $this->id . '-' . Auth::user()->id . '-' . sha1(time()) . '.' . strtolower($file->getClientOriginalExtension());
        $path_name = $path . '/' . $name;
        $file->move($path, $name);

        // resize the image to a width of 1024 and constrain aspect ratio (auto height)
        /*if (exif_imagetype($path_name)) {
            Image::make(url($path_name))
                ->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($path_name);
        } //else
        //  Toastr::error("Bad image");*/

        $this->attachment = $name;
        $this->save();
    }

    /**
     * Email Issue
     */
    public function emailTicket($action)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';
        if (\App::environment('prod', 'dev')) {
            $email_to[] = "kirstie@capecod.com.au";
            $email_user = (Auth::check() && validEmail($this->createdBy->email)) ? $this->createdBy->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Misc\SupportTicketCreated($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Misc\SupportTicketCreated($this, $action));
    }

    /**
     * Get the priority text   (getter)
     */
    public function getPriorityTextAttribute()
    {
        if ($this->attributes['priority'] == '1')
            return 'low';
        if ($this->attributes['priority'] == '2')
            return 'med';
        if ($this->attributes['priority'] == '3')
            return 'high';
        if ($this->attributes['priority'] == '4')
            return 'in progress';
        return "none";

    }

    /**
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'])
            return '/filebank/support/ticket/' . $this->attributes['attachment'];

        return '';
    }
    /**
     * Get the owner of record  (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->company;
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