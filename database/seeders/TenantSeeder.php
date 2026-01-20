<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Check if tenant already exists
        $tenant = Tenant::find('test-gym');
        
        if (!$tenant) {
            $tenant = Tenant::create([
                'id' => 'test-gym',
                'name' => 'Test Gym',
                'email' => 'test@gym.com',
                'phone' => '1234567890',
                'subscription_plan' => 'basic',
                'active' => true,
            ]);
        }

        // Check if domain already exists, if not create it
        $domain = $tenant->domains()->where('domain', '127.0.0.1')->first();
        if (!$domain) {
            $tenant->domains()->create(['domain' => '127.0.0.1']);
        }
    }
} 