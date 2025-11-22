<?php

namespace App\Console\Commands\Syncs\BS;


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
use App\Models\Suppliers\Supplier;
use App\Models\Users\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchInventoryOnlyNew extends FetchInventory
{
    protected $signature = 'sync:bs_inventory_only_new';

    public function exec() :void
    {
        $this->fetchInventoryData();
    }
    protected function fetchInventoryData(): void
    {
        $users = User::query()->get()->pluck('id','origin_id');

        $bsRoles = [
            'BodyShopMechanic' => MechanicRole::NAME,
            'BodyShopAdmin'  => AdminRole::NAME,
            'BodyShopSuperAdmin'  => SuperAdminRole::NAME,
        ];
        $messages = [
            'history.bs.inventory_created' => InventoryHistoryService::HISTORY_MESSAGE_CREATED,
            'history.bs.inventory_changed' => InventoryHistoryService::HISTORY_MESSAGE_UPDATED,
            'history.bs.inventory_quantity_increased' => InventoryHistoryService::HISTORY_MESSAGE_INVENTORY_QUANTITY_INCREASED,
            'history.bs.inventory_quantity_decreased' => InventoryHistoryService::HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED,
            'history.bs.inventory_quantity_decreased_sold' => InventoryHistoryService::HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED_SOLD,
            'history.bs.inventory_quantity_reserved_for_order' => InventoryHistoryService::HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_FOR_ORDER,
            'history.bs.inventory_quantity_reserved_additionally_for_order' => InventoryHistoryService::HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_ADDITIONALLY_FOR_ORDER,
            'history.bs.inventory_quantity_reduced_from_order' => InventoryHistoryService::HISTORY_MESSAGE_INVENTORY_QUANTITY_REDUCED_FROM_ORDER,
            'history.bs.inventory_price_changed_for_order' => InventoryHistoryService::HISTORY_MESSAGE_INVENTORY_PRICE_CHANGED_FOR_ORDER,
            'history.bs.finished_order_with_inventory' => InventoryHistoryService::HISTORY_MESSAGE_FINISHED_ORDER_WITH_INVENTORY,
            'history.bs.inventory_quantity_returned_for_deleted_order' => InventoryHistoryService::HISTORY_MESSAGE_INVENTORY_QUANTITY_RETURNED_FOR_DELETED_ORDER,
        ];

        $countUpdated = 0;
        $countCreated = 0;
        echo "[x] START... fetch inventory" . PHP_EOL;
        $supplier = Supplier::query()->whereNotNull('origin_id')->get()->pluck('id', 'origin_id');
        $category = Category::query()->whereNotNull('origin_id')->get()->pluck('id', 'origin_id');

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
                if (!Inventory::query()->where('origin_id', $item->id)->exists()) {
                    $countCreated++;
                    $inventory = $this->createInventory($item,$category,$supplier);

                    $historiesOld = DbConnections::haulk()
                        ->table('histories')
                        ->where('model_type', 'App\Models\BodyShop\Inventories\Inventory')
                        ->where('model_id', $inventory->origin_id)
                        ->orderBy('performed_at')
                        ->get();

                    foreach ($historiesOld as $key => $history) {
                        $h = new History();
                        $h->model_type = Inventory::MORPH_NAME;
                        $h->model_id = $inventory->id;
                        $h->type = $history->type == 1 ? HistoryType::CHANGES : HistoryType::ACTIVITY;

                        if(array_key_exists($history->user_id, $users->toArray())){
                            $h->user_id = $users->toArray()[$history->user_id];
                            $h->user_role = $bsRoles[$history->user_role];
                        }

                        $h->msg = $messages[$history->message];
                        $h->msg_attr = json_to_array($history->meta);
                        $h->details = json_to_array($history->histories);
                        $h->performed_at = $history->performed_at;
                        $h->performed_timezone = $history->performed_timezone;

                        $h->save();
                    }

                    $transactions = DbConnections::haulk()
                        ->table('bs_inventory_transactions')
                        ->where('inventory_id', $inventory->origin_id)
                        ->whereNull('order_id')
                        ->get();

                    foreach ($transactions as $transaction) {
                        $t = new Transaction();
                        $t->inventory_id = $inventory->id;
                        $t->transaction_date = $transaction->transaction_date;
                        $t->quantity = $transaction->quantity;
                        $t->price = $transaction->price;
                        $t->invoice_number = $transaction->invoice_number;
                        $t->describe = $transaction->describe;
                        $t->operation_type = $transaction->operation_type;
                        $t->is_reserve = $transaction->is_reserve;
                        $t->created_at = $transaction->created_at;
                        $t->updated_at = $transaction->updated_at;
                        $t->discount = $transaction->discount;
                        $t->tax = $transaction->tax;
                        $t->payment_date = $transaction->payment_date;
                        $t->first_name = $transaction->first_name;
                        $t->last_name = $transaction->last_name;
                        $t->phone = $transaction->phone;
                        $t->email = $transaction->email;
                        $t->company_name = $transaction->company_name;
                        $t->payment_method = $transaction->payment_method;
                        $t->origin_id = $transaction->id;
                        $t->save();
                    }
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

    private function createInventory($item,$category,$supplier): Inventory
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
        return $i;
    }
}
