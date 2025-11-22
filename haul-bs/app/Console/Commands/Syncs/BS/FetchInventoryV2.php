<?php

namespace App\Console\Commands\Syncs\BS;


use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Suppliers\Supplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchInventoryV2 extends FetchInventory
{

    protected $signature = 'sync:bs_inventory_v2';

    public function exec() :void
    {
        \Artisan::call(FetchSupplier::class);
        $this->fetchUnit();
        $this->fetchCategory();
        $this->fetchInventoryData();
    }
    protected function fetchInventoryData(): void
    {
        $countUpdated = 0;
        $countCreated = 0;
        echo "[x] START... fetch inventory" . PHP_EOL;
        $supplier = Supplier::query()->whereNotNull('origin_id')->get()->pluck('id', 'origin_id');
        $category = Category::query()->whereNotNull('origin_id')->get()->pluck('id', 'origin_id');
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
                $inventory = Inventory::query()->where('article_number', $item->stock_number)->first();
                if ($inventory) {
                    $countUpdated++;
                    $inventory->quantity = $item->quantity;
                    $inventory->min_limit = $item->min_limit;
                    $inventory->supplier_id = $item->supplier_id
                        ? Arr::get($supplier,$item->supplier_id)
                        : null;
                    $inventory->origin_id = $item->id;
                    $inventory->for_shop = 1;
                    $inventory->save();
                } else {
                    $countCreated++;
                    $this->createInventory($item,$category,$supplier);
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

    private function createInventory($item,$category,$supplier): void
    {
        $i = new Inventory();
        $i->name = $item->name;
        $i->slug = $this->slug(Str::slug($item->name));
        $i->article_number = $item->stock_number;
        $i->stock_number = 'empty';
        $i->price_retail = $item->price_retail;
        $i->quantity = $item->quantity;
        $i->notes = $item->notes;
        $i->created_at = $item->created_at;
        $i->new_item = true;
        $i->updated_at = $item->updated_at;
        $i->min_limit = $item->min_limit;
        $i->deleted_at = $item->deleted_at;
        $i->for_shop = 0;
        $i->length = $item->length;
        $i->width = $item->width;
        $i->height = $item->height;
        $i->weight = $item->weight;
        $i->min_limit_price = $item->min_limit_price;
        $i->unit_id = $item->unit_id;
        $i->supplier_id = $item->supplier_id
            ? Arr::get($supplier,$item->supplier_id)
            : null;
        $i->origin_id = $item->id;
        $i->category_id = $item->category_id
            ? Arr::get($category,$item->category_id)
            : null;

        $i->save();

        $seo = new Seo();
        $seo->model_type = Inventory::MORPH_NAME;
        $seo->model_id = $i->id;
        $seo->save();
    }
}
