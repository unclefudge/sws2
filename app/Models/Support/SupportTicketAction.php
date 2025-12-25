<?php

namespace App\Models\Support;

use App\Models\Misc\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;

class SupportTicketAction extends Model
{

    protected $table = 'support_tickets_actions';
    protected $fillable = ['ticket_id', 'action', 'attachment', 'created_by', 'created_at'];

    /**
     * A SupportTicketAction belongs to a site issue
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticket()
    {
        return $this->belongsTo('App\Models\Support\SupportTicket', 'ticket_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_id')->where('table', 'support_ticket_action');
    }

    /**
     * A SupportTicketAction belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'created_by');
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
     * Email Action Notification
     */
    public function emailAction()
    {
        $ticket = SupportTicket::findOrFail($this->ticket_id);

        $email_to = [env('EMAIL_DEV')];
        if (\App::environment('prod', 'dev')) {
            $email_to[] = "kirstie@capecod.com.au";
            if ($ticket && validEmail($ticket->createdBy->email))
                $email_to[] = $ticket->createdBy->email; // email ticket owner

            // Email user who updated ticket
            if (Auth::check() && validEmail($this->createdBy->email) && !in_array($this->createdBy->email, $email_to))
                $email_to[] = $this->createdBy->email;
        }

        Mail::to($email_to)->send(new \App\Mail\Misc\SupportTicketUpdated($ticket, $this));
    }


    /**
     * Get the owner of record  (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->ticket->company;
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