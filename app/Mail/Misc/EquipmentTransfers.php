<?php

namespace App\Mail\Misc;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EquipmentTransfers extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $file_attachment, $log;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file_attachment, $log)
    {
        $this->file_attachment = $file_attachment;
        $this->log = $log;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->file_attachment && file_exists($this->file_attachment))
            return $this->markdown('emails/misc/equipment-transfers')->subject('SafeWorksite - Equipment Transfers')->attach($this->file_attachment);

        return $this->markdown('emails/misc/equipment-transfers')->subject('SafeWorksite - Equipment Transfers');
    }
}
