<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateTestCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-company';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test company for multi-tenant testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $company = new \App\Models\Company();
        $company->name = 'Test Company';
        $company->slug = 'test-company';
        $company->save();

        $this->info('Test company created successfully!');
        $this->info('Name: ' . $company->name);
        $this->info('Slug: ' . $company->slug);
        $this->info('Access URL: /admin/' . $company->slug);
    }
}
