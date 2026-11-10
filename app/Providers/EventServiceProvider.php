<?php

namespace App\Providers;

use App\Events\AssignmentPublished;
use App\Events\SubmissionGraded;
use App\Listeners\SendAssignmentPublishedNotification;
use App\Listeners\SendSubmissionGradedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AssignmentPublished::class => [
            SendAssignmentPublishedNotification::class,
        ],
        SubmissionGraded::class => [
            SendSubmissionGradedNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}

