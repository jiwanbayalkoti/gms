<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        $superAdmin = User::where('email', 'superadmin@gym.com')->first();
        
        if (!$superAdmin) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@gym.com',
                'password' => Hash::make('superadmin123'),
                'role' => 'SuperAdmin',
                'phone' => null,
                'active' => true,
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Super Admin user created successfully!');
            $this->command->info('Email: superadmin@gym.com');
            $this->command->info('Password: superadmin123');
        } else {
            $this->command->info('Super Admin user already exists.');
        }
    }
}
