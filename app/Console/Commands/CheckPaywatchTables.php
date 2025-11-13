<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPaywatchTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-paywatch-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check paywatch tables status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = DB::select("SHOW TABLES LIKE 'paywatch%'");
        
        $this->info('Tablas Paywatch:');
        foreach ($tables as $table) {
            foreach ($table as $tableName) {
                $this->info('- ' . $tableName);
            }
        }
        
        // Verificar tablas específicas
        $requiredTables = ['usuario', 'all_notifications', 'payment_notifications', 'companies', 'users', 'company_user', 'company_device', 'cashier_device_access'];
        
        $this->info(PHP_EOL . 'Verificación de tablas requeridas:');
        foreach ($requiredTables as $table) {
            $exists = DB::select("SHOW TABLES LIKE '$table'");
            if ($exists) {
                $count = DB::table($table)->count();
                $this->info("✓ $table ($count registros)");
            } else {
                $this->error("✗ $table (no existe)");
            }
        }
    }
}
