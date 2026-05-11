<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use STS\FilamentImpersonate\Events\EnterImpersonation;
use STS\FilamentImpersonate\Events\LeaveImpersonation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(EnterImpersonation::class, function (EnterImpersonation $event): void {
            AuditLog::create([
                'user_id' => $event->impersonator->getKey(),
                'action' => 'impersonation_start',
                'entity_type' => 'user',
                'entity_id' => $event->impersonated->getKey(),
                'old_status' => null,
                'new_status' => null,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        });

        Event::listen(LeaveImpersonation::class, function (LeaveImpersonation $event): void {
            AuditLog::create([
                'user_id' => $event->impersonator->getKey(),
                'action' => 'impersonation_end',
                'entity_type' => 'user',
                'entity_id' => $event->impersonated->getKey(),
                'old_status' => null,
                'new_status' => null,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        });
    }
}
