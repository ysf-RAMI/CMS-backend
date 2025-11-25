<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheInvalidationService;

class ClearAppCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all application-specific caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing application caches...');

        CacheInvalidationService::clearAll();

        $this->info('All application caches cleared successfully!');
    }
}
