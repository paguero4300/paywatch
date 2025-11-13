<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = new \App\Models\User();
        $user->name = 'Super Admin';
        $user->email = 'admin@paywatch.com';
        $user->password = \Hash::make('password');
        $user->is_super_admin = true;
        $user->save();

        $this->info('Super Admin user created successfully!');
        $this->info('Email: admin@paywatch.com');
        $this->info('Password: password');
    }
}
