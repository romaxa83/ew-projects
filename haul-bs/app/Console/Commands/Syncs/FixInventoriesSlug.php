<?php

namespace App\Console\Commands\Syncs;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\History\Services\InventoryHistoryService;
use App\Foundations\Modules\Permission\Roles\AdminRole;
use App\Foundations\Modules\Permission\Roles\MechanicRole;
use App\Foundations\Modules\Permission\Roles\SuperAdminRole;
use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Inventories\Unit;
use App\Models\Suppliers\Supplier;
use App\Models\Users\User;
use App\Services\Events\EventService;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;

class FixInventoriesSlug extends BaseCommand
{
    protected $signature = 'sync:bs_inventory_fix_slug';

    public function exec(): void
    {
        $this->fixInventorySlug();
    }

    protected function fixInventorySlug(): void
    {
        echo "[x] START... fix slug" . PHP_EOL;

        try {
            $inventories = Inventory::query()->get();

            $progressBar = new ProgressBar($this->output, count($inventories));
            $progressBar->setFormat('verbose');
            $progressBar->start();
            /** @var Inventory[] $inventories */
            foreach ($inventories as $k => $item){
                $item->slug = $this->slug(Str::slug($item->name));
                $item->save();

                EventService::inventory($item)
                    ->update()
                    ->sendToEcomm()
                    ->exec();
                $progressBar->advance();
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch fetch inventory" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }

    public function slug($slug)
    {
        if(Inventory::query()->withTrashed()->where('slug', $slug)->exists()){
            $slug .= '-' .random_int(1, 9999);
            self::slug($slug);
        }

        return $slug;
    }
}

