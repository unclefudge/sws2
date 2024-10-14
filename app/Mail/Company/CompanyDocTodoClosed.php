<?php

namespace App\Mail\Company;

use App\Models\Comms\Todo;
use App\Models\Company\CompanyDoc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyDocTodoClosed extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $doc, $todo;

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
