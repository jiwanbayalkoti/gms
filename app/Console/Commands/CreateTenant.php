<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {id} {domain}';
    protected $description = 'Create a new tenant with a domain';

    public function handle()
    {
        DB::beginTransaction();
        try {
            $id = $this->argument('id');
            $domain = $this->argument('domain');

            // Insert tenant directly into database
            DB::table('tenants')->insert([
                'id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get the tenant
            $tenant = Tenant::find($id);

            // Create domain
            $tenant->domains()->create(['domain' => $domain]);

            DB::commit();
            $this->info('Tenant created successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
} 