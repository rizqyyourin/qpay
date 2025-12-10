<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class CleanupExpiredReservations extends Command
{
    protected $signature = 'inventory:cleanup-reservations';
    protected $description = 'Remove expired inventory reservations (older than 30 minutes)';

    public function handle()
    {
        $count = InventoryService::cleanupExpiredReservations();
        
        $this->info("Cleaned up {$count} expired reservations");
        
        return Command::SUCCESS;
    }
}
