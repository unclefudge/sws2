<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        \Illuminate\Mail\Events\MessageSending::class => [
            // Apply admin-managed To/CC/BCC rules before the final envelope is
            // captured, so history shows exactly who received the message.
            \App\Listeners\Scheduled\ApplyScheduledRecipientRules::class,
            \App\Listeners\Scheduled\CaptureScheduledMessageSending::class,
        ],
        \Illuminate\Mail\Events\MessageSent::class => [
            \App\Listeners\Scheduled\CaptureScheduledMessageSent::class,
        ],
        \Illuminate\Queue\Events\JobProcessing::class => [
            'App\Listeners\Scheduled\ScheduledQueueContext@processing',
        ],
        \Illuminate\Queue\Events\JobProcessed::class => [
            'App\Listeners\Scheduled\ScheduledQueueContext@processed',
        ],
        \Illuminate\Queue\Events\JobExceptionOccurred::class => [
            'App\Listeners\Scheduled\ScheduledQueueContext@exceptionOccurred',
        ],
        \Illuminate\Queue\Events\JobFailed::class => [
            'App\Listeners\Scheduled\ScheduledQueueContext@failed',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
