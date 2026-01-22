<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home route - handles both central and tenant domains
Route::middleware(['web'])->get('/', function () {
    $host = request()->getHost();
    $centralDomains = config('tenancy.central_domains', []);
    
    // Always log for debugging (helps in production too)
    \Log::info('Home route accessed', [
        'host' => $host,
        'central_domains' => $centralDomains,
        'is_central' => in_array($host, $centralDomains),
        'app_debug' => config('app.debug')
    ]);
    
    // If on central domain, show welcome page
    if (in_array($host, $centralDomains)) {
        \Log::info('Showing welcome page for central domain', ['host' => $host]);
        return view('welcome');
    }
    
    // For tenant domains, try to find and initialize tenant
    try {
        // Try to find tenant domain in central database
        $tenant = \App\Models\Tenant::query()
            ->whereHas('domains', function ($query) use ($host) {
                $query->where('domain', $host);
            })
            ->first();
            
        if ($tenant) {
            \Log::info('Tenant found, initializing tenancy', [
                'host' => $host,
                'tenant_id' => $tenant->id
            ]);
            
            // Initialize tenancy
            tenancy()->initialize($tenant);
            
            // If user is authenticated, redirect to dashboard
            if (auth()->check()) {
                return redirect()->route('dashboard');
            }
            
            // Otherwise, show tenant home page
            return view('tenant.home');
        } else {
            // Tenant not found - log this important info
            \Log::warning('Tenant not found for domain', [
                'domain' => $host,
                'message' => 'No tenant found with this domain. Please check if tenant exists in database and domain is properly configured.'
            ]);
        }
    } catch (\Exception $e) {
        // Always log errors (even in production) for debugging
        \Log::error('Tenancy initialization failed on home route', [
            'domain' => $host,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    // Fallback: show welcome page if tenant not found or error occurred
    // Check logs/storage/logs/laravel.log for details
    return view('welcome');
})->name('home');

// Central domain routes - Gym Management (SuperAdmin only)
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::resource('gyms', App\Http\Controllers\GymController::class);
        Route::post('gyms/status/{gym}', [App\Http\Controllers\GymController::class, 'updateStatus'])->name('gyms.status');
        Route::get('gyms/{gym}/create-admin', [App\Http\Controllers\GymController::class, 'createAdmin'])->name('gyms.create-admin');
        Route::post('gyms/{gym}/create-admin', [App\Http\Controllers\GymController::class, 'storeAdmin'])->name('gyms.store-admin');
    });
});

// Tenant routes (gym-specific routes) - only accessible on tenant domains
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Authentication routes (accessible without auth middleware)
    // Rate limit login attempts: 5 attempts per minute per IP
    Route::middleware(['guest', 'throttle:5,1'])->group(function () {
        Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    });

    // Logout route (accessible with auth middleware)
    Route::middleware('auth')->post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

    // Tenant specific routes should go here
    Route::middleware(['auth'])->group(base_path('routes/tenant.php'));
});

// API Login route (accessible without tenancy for initial login)
Route::middleware(['web'])->prefix('api/v1')->group(function () {
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('api.login');
});

// Tenant API routes (accessible on tenant domains with Sanctum auth)
// All routes except login require tenancy initialization
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api/v1')->group(function () {
    // Load all API routes except login (which is defined above)
    require base_path('routes/api-tenant.php');
});
