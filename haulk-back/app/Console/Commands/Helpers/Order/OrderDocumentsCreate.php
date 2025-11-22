<?php

namespace App\Console\Commands\Helpers\Order;

use App\Models\Orders\Order;
use App\Services\Orders\OrderSearchService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class OrderDocumentsCreate extends Command
{
    protected $signature = 'es:order_documents_create';

    public function handle(): int
    {
        /** @var $orderService OrderSearchService */
        $orderService = resolve(OrderSearchService::class);

        $count = Order::count();
        $progressBar = new ProgressBar($this->output, $count);
        $progressBar->setFormat('verbose');

        try {
            $progressBar->start();

            Order::query()->chunk(10, function ($orders) use ($orderService, $progressBar) {
                foreach ($orders as $order) {
                    $document = $orderService->handleSaveOrderData($order);
                    $progressBar->advance();
                }
            });

            $progressBar->finish();
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $progressBar->clear();
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
