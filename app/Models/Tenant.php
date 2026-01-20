<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\MaintenanceMode;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, MaintenanceMode;

    public static function getCustomColumns(): array
    {
        return [
            'name',
            'email',
            'phone',
            'logo',
            'subscription_plan',
            'active',
            'subscription_ends_at',
        ];
    }

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'subscription_ends_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($tenant) {
            if (!$tenant->id) {
                $tenant->id = $tenant->getKey();
            }
        });
    }
}
