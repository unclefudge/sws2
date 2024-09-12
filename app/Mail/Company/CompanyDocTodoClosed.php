<?php

namespace App\Mail\Company;

use App\Models\Company\CompanyDoc;
use App\Models\Comms\Todo;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyDocTodoClosed extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    public $doc, todo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CompanyDoc $doc, Todo $todo)
    {
        $this->doc = $doc;
        $this->todo = $todo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails/company/doc-todo-closed')->subject("SafeWorksite - Document ToDo Closed");
    }
}
