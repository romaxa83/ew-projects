<?php

namespace App\Console\Commands;

use App\Models\Orders\Order;
use App\Services\Orders\CompanySearchService;
use App\Services\Orders\OrderSearchService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class OrderPaymentCalculate extends Command
{
    private const CHUNK = 1000;
    protected $signature = 'orders:calculate-payment {--after-date= : Started date (Y-m-d)}';
    protected $description = 'Calculate payment data';

    public function handle(): void
    {
        $query = Order::withoutGlobalScopes()
            ->orderBy('id');
        if ($this->option('after-date')) {
            $query = $query
                ->where(
                    'created_at',
                    '>=',
                    Carbon::createFromFormat('Y-m-d', $this->option('after-date'))->toDateTimeString()
                );
        }
        $bar = $this->output->createProgressBar($query->count());
        $bar->start();
        $orderService = resolve(OrderSearchService::class);
        $companyService = resolve(CompanySearchService::class);

        $query
            ->chunk(
                self::CHUNK,
                static function (Collection $orders) use ($orderService, $companyService, $bar): void {
                    $documents = [];
                    foreach ($orders as $order) {
                        $documents[] = $orderService->handleSaveOrderData($order);
                    }
                    sleep(1);
                    foreach ($documents as $document) {
                        $companyService->handleCalculateCompany($document, false);
                    }
                    $bar->advance(self::CHUNK);
                }
            );
        $bar->finish();
    }
}
