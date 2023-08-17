<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Mail;

class SupportTicketActionFile extends Model {

    protected $table = 'support_tickets_actions_files';
    protected $fillable = ['action_id', 'type', 'name', 'attachment', 'notes'];

    /**
     * A SupportTicketActionFile belongs to a SupportTicketAction issue
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function action()
    {
        return $this->belongsTo('App\Models\Support\SupportTicketAction', 'action_id');
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
        return $this->ticket->company;
    }
}