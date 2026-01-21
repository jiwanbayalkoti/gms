<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create 
                            {id : The unique identifier for the tenant}
                            {domain : The domain for the tenant}
                            {--name= : The name of the gym/tenant}
                            {--email= : The email address}
                            {--phone= : The phone number}
                            {--migrate : Run migrations after creating tenant database}';

    protected $description = 'Create a new tenant with a domain and optionally run migrations';

    public function handle()
    {
        try {
            $id = $this->argument('id');
            $domain = $this->argument('domain');
            $name = $this->option('name') ?: $id;
            $email = $this->option('email');
            $phone = $this->option('phone');

            // Check if tenant already exists
            if (Tenant::find($id)) {
                $this->error("Tenant with ID '{$id}' already exists!");
                return Command::FAILURE;
            }

            // Check if domain already exists
            $existingDomain = DB::table('domains')->where('domain', $domain)->first();
            if ($existingDomain) {
                $this->error("Domain '{$domain}' is already assigned to another tenant!");
                return Command::FAILURE;
            }

            $this->info("Creating tenant '{$id}' with domain '{$domain}'...");

            // Create tenant using model (this will trigger database creation)
            $tenant = Tenant::create([
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'active' => true,
            ]);

            // Create domain
            $tenant->domains()->create(['domain' => $domain]);

            $this->info("✓ Tenant created successfully!");
            $this->info("✓ Domain '{$domain}' assigned to tenant '{$id}'");
            $this->info("✓ Tenant database 'tenant{$id}' created");

            // Run migrations if requested
            if ($this->option('migrate')) {
                $this->info("Running migrations for tenant database...");
                $this->call('tenants:migrate', [
                    '--tenants' => [$id]
                ]);
                $this->info("✓ Migrations completed!");
            } else {
                $this->warn("Note: Don't forget to run migrations: php artisan tenants:migrate --tenants={$id}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create tenant: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
} 