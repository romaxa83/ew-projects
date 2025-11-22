<?php

namespace App\Console\Commands\Workers;

use App\Documents\OrderDocument;
use App\Models\Orders\Order;
use App\Services\Orders\OrderSearchService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class OrderDocumentReindex extends Command
{
    protected $signature = 'worker:order_document_reindex';

    private $countUpdate = 0;
    private $countDelete = 0;

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            logger_info("[worker] Order document reindex [time = {$time}], [update = $this->countUpdate, delete = $this->countDelete]");
            $this->info("Done [time = {$time}], [update = $this->countUpdate, delete = $this->countDelete]");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info('[worker] Order document reindex FAIL', [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }

    }

    private function exec()
    {
        $chunk = 200;
        $count = OrderDocument::query()->count();

        $progressBar = new ProgressBar($this->output, $count);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $t = (int)ceil($count / $chunk);
        /** @var $orderService OrderSearchService */
        $orderService = resolve(OrderSearchService::class);

        try {
            for ($i = 0; $i < $t; $i++) {
                $progressBar->advance($chunk);
                $from = $i * $chunk;

                $docs = OrderDocument::query()
                    ->size($chunk)
                    ->from($from)
                    ->search(
                        [
                            OrderDocument::id(),
                            OrderDocument::pickupPlannedDate(),
                            OrderDocument::deliveryPlannedDate(),
                        ]
                    )
                    ->keyBy('id')
                ;

                foreach ($docs as $id => $doc){
                    if($order = Order::query()->where('id', $id)->first()){
                        $orderService->handleSaveOrderData($order);
                        $this->countUpdate++;
                    } else {
                        OrderDocument::query()->delete($id);
                        $this->countDelete++;
                    }
                }
            }

        } catch (\Throwable $e) {
//            $progressBar->clear();
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
    }
}
