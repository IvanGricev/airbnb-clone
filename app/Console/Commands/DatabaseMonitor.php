<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseMonitor extends Command
{
    protected $signature = 'db:monitor {--timeout=5}';
    protected $description = 'Monitor database connection';

    public function handle()
    {
        $timeout = $this->option('timeout');
        
        try {
            DB::connection()->getPdo();
            $this->info('Database connection successful');
            return 0;
        } catch (\Exception $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
            return 1;
        }
    }
} 