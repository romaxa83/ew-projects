<?php

namespace App\Console\Commands\Syncs\BS;

use App\Foundations\Helpers\DbConnections;
use App\Models\Inventories\Inventory;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchInventoryForShop extends FetchInventory
{

    protected $signature = 'sync:bs_inventory_for_shop';

    public function exec() :void
    {
        $this->fetchInventoryData();
    }
    protected function fetchInventoryData(): void
    {
        $countUpdated = 0;
        $countCreated = 0;
        echo "[x] START... fetch inventory" . PHP_EOL;
        Inventory::query()->update(['for_shop' => 0]);
        try {
            $data = DbConnections::haulk()
                ->table('bs_inventories')
                ->get()
                ->toArray();

            $progressBar = new ProgressBar($this->output, count($data));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($data as $item) {
                /** @var Inventory $inventory */
                $inventory = Inventory::query()->where('article_number', $item->stock_number)->where('stock_number', '<>', 'empty')->first();
                if ($inventory) {
                    $countUpdated++;
                    $inventory->for_shop = 1;
                    $inventory->save();
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "countUpdated " . $countUpdated .PHP_EOL;
            echo "countCreated " . $countCreated .PHP_EOL;
            echo PHP_EOL;
            echo "[x]  DONE fetch fetch inventory" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
