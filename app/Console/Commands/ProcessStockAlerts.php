<?php

namespace App\Console\Commands;

use App\Services\StockAlertService;
use Illuminate\Console\Command;

class ProcessStockAlerts extends Command
{
    protected $signature = 'stock:notify';

    protected $description = 'Notify customers when subscribed products are back in stock';

    public function handle(StockAlertService $service): int
    {
        $count = $service->processBackInStock();
        $this->info("Back-in-stock notifications sent: {$count}");

        return self::SUCCESS;
    }
}
