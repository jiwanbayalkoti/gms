<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Middleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set up the tenant routes
        $this->configureRoutes();

        // Handle tenant creation events
        $this->configureTenantEvents();
    }

    /**
     * Configure the routes for multi-tenancy.
     */
    protected function configureRoutes()
    {
        // Central domains (admin panel for SaaS) - only accessible on central domains
        Route::middleware([
            'web',
            PreventAccessFromCentralDomains::class,
            'auth',
        ])->prefix('admin')->group(function () {
            Route::get('/tenants', function () {
                return Tenant::all();
            })->name('tenants.list');
        });
    }

    /**
     * Configure tenant events.
     */
    protected function configureTenantEvents()
    {
        // When a tenant is created, set up the database with default data
        Event::listen(Events\TenantCreated::class, function (Events\TenantCreated $event) {
            // You can add custom initialization code here
            // For example: create default roles, default admin user, etc.
        });
    }
}
