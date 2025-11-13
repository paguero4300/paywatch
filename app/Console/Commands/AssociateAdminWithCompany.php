<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AssociateAdminWithCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:associate-admin-with-company';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate super admin user with test company';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = \App\Models\User::where('email', 'admin@paywatch.com')->first();
        $company = \App\Models\Company::where('slug', 'test-company')->first();

        if (!$user) {
            $this->error('Super admin user not found!');
            return;
        }

        if (!$company) {
            $this->error('Test company not found!');
            return;
        }

        // Associate user with company as admin
        $user->companies()->attach($company->id, ['role' => 'admin']);

        $this->info('Super admin associated with test company successfully!');
        $this->info('User: ' . $user->email);
        $this->info('Company: ' . $company->name);
        $this->info('Role: admin');
    }
}
