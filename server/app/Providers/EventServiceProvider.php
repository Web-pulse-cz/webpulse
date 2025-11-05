<?php

namespace App\Providers;

use App\Events\BiographySaved;
use App\Events\CareerApplicationSaved;
use App\Events\ContactUpdatedEvent;
use App\Events\DemandSaved;
use App\Events\EventRegistrationSaved;
use App\Events\ProjectSavedEvent;
use App\Events\ProjectSavedListener;
use App\Listeners\BiographyGenerator;
use App\Listeners\CareerApplicationEmail;
use App\Listeners\ContactUpdated;
use App\Listeners\DemandEmail;
use App\Listeners\EventRegistrationEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ContactUpdatedEvent::class => [
            ContactUpdated::class
        ],
        ProjectSavedEvent::class => [
            ProjectSavedListener::class
        ],
        DemandSaved::class => [
            DemandEmail::class
        ],
        EventRegistrationSaved::class => [
            EventRegistrationEmail::class,
        ],
        CareerApplicationSaved::class => [
            CareerApplicationEmail::class,
        ],
        BiographySaved::class => [
            BiographyGenerator::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
