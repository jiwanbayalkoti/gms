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
    
    // Priority: If on central domain, show welcome page
    if (in_array($host, $centralDomains)) {
        return view('welcome');
    }
    
    // Otherwise, try to initialize tenancy for tenant domain
    try {
        $tenant = \App\Models\Tenant::query()
            ->whereHas('domains', function ($query) use ($host) {
                $query->where('domain', $host);
            })
            ->first();
            
        if ($tenant) {
            tenancy()->initialize($tenant);
            return view('tenant.home');
        }
    } catch (\Exception $e) {
        // If tenancy initialization fails, show welcome as fallback
    }
    
    // Fallback: show welcome page
    return view('welcome');
});

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
