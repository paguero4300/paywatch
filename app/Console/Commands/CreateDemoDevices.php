<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateDemoDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-demo-devices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create demo devices for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $company = \App\Models\Company::where('slug', 'test-company')->first();
        
        if (!$company) {
            $this->error('Test company not found!');
            return;
        }

        // Create demo devices
        $devices = [
            ['username' => 'caja01', 'device_id' => 'POS001'],
            ['username' => 'caja02', 'device_id' => 'POS002'],
            ['username' => 'caja03', 'device_id' => 'POS003'],
        ];

        foreach ($devices as $deviceData) {
            $device = new \App\Models\Device();
            $device->username = $deviceData['username'];
            $device->password_hash = \Hash::make('password');
            $device->device_id = $deviceData['device_id'];
            $device->save();

            // Associate device with company
            $device->company()->attach($company->id);

            $this->info('Device created: ' . $deviceData['device_id'] . ' (' . $deviceData['username'] . ')');
        }

        $this->info('Demo devices created successfully!');
    }
}
