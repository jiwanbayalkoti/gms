<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Domain;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckTenant extends Command
{
    protected $signature = 'tenant:check {domain : The domain to check}';
    protected $description = 'Check if a domain has a tenant assigned, and optionally create/fix it';

    public function handle()
    {
        $domain = $this->argument('domain');
        
        $this->info("Checking domain: {$domain}");
        $this->newLine();
        
        // Check if domain exists
        $domainRecord = Domain::where('domain', $domain)->first();
        
        if ($domainRecord) {
            $tenant = $domainRecord->tenant;
            $this->info("✓ Domain found!");
            $this->info("  Tenant ID: {$tenant->id}");
            $this->info("  Tenant Name: {$tenant->name}");
            $this->info("  Active: " . ($tenant->active ? 'Yes' : 'No'));
            return Command::SUCCESS;
        }
        
        $this->warn("✗ Domain not found in database!");
        $this->newLine();
        
        // Check if user wants to create it
        if ($this->confirm('Do you want to create a tenant for this domain?', true)) {
            $id = $this->ask('Enter tenant ID (e.g., gms-jbtech)', 'gms-jbtech');
            $name = $this->ask('Enter tenant name', 'GMS JB Tech');
            $email = $this->ask('Enter email (optional)', '');
            $phone = $this->ask('Enter phone (optional)', '');
            
            // Check if tenant ID already exists
            if (Tenant::find($id)) {
                $this->error("Tenant with ID '{$id}' already exists!");
                if ($this->confirm('Do you want to add this domain to the existing tenant?', true)) {
                    $tenant = Tenant::find($id);
                    $tenant->domains()->create(['domain' => $domain]);
                    $this->info("✓ Domain '{$domain}' added to tenant '{$id}'");
                    return Command::SUCCESS;
                }
                return Command::FAILURE;
            }
            
            try {
                // Use the same approach as CreateTenant command
                // The BaseTenant model handles id specially, so we use create() with id in array
                $tenant = Tenant::create([
                    'id' => $id,
                    'name' => $name,
                    'email' => $email ?: null,
                    'phone' => $phone ?: null,
                    'active' => true,
                ]);
                
                $tenant->domains()->create(['domain' => $domain]);
                
                $this->info("✓ Tenant created successfully!");
                $this->info("✓ Domain '{$domain}' assigned to tenant '{$id}'");
                $this->newLine();
                $this->warn("Don't forget to run migrations:");
                $this->line("  php artisan tenants:migrate --tenants={$id}");
                
                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error('Failed to create tenant: ' . $e->getMessage());
                $this->error('Stack trace: ' . $e->getTraceAsString());
                
                // If create() fails, try alternative approach using DB directly
                $this->warn('Trying alternative method...');
                try {
                    DB::beginTransaction();
                    
                    // Insert tenant directly
                    DB::table('tenants')->insert([
                        'id' => $id,
                        'name' => $name,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null,
                        'active' => true,
                        'data' => json_encode(['id' => $id]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Create domain
                    DB::table('domains')->insert([
                        'domain' => $domain,
                        'tenant_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    DB::commit();
                    
                    $this->info("✓ Tenant created successfully using alternative method!");
                    $this->info("✓ Domain '{$domain}' assigned to tenant '{$id}'");
                    $this->newLine();
                    $this->warn("Don't forget to run migrations:");
                    $this->line("  php artisan tenants:migrate --tenants={$id}");
                    
                    return Command::SUCCESS;
                } catch (\Exception $e2) {
                    DB::rollBack();
                    $this->error('Alternative method also failed: ' . $e2->getMessage());
                    return Command::FAILURE;
                }
            }
        }
        
        return Command::SUCCESS;
    }
}

