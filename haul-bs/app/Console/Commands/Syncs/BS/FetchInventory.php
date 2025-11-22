<?php

namespace App\Console\Commands\Syncs\BS;

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
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchInventory extends BaseCommand
{
    protected $signature = 'sync:bs_inventory';

    public function exec(): void
    {
        $this->fetchUnit();
        $this->fetchCategory();
        $this->fetchInventory();
    }

    protected function fetchInventory(): void
    {
        echo "[x] START... fetch inventory" . PHP_EOL;

        $users = User::query()->get()->pluck('id','origin_id');
        $supplier = Supplier::query()->whereNotNull('origin_id')->get()->pluck('id','origin_id');
        $category = Category::query()->whereNotNull('origin_id')->get()->pluck('id','origin_id');

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

        try {
            $data = DbConnections::haulk()
                ->table('bs_inventories')
                ->get()
                ->toArray()
            ;

            $progressBar = new ProgressBar($this->output, count($data));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            $histories = DbConnections::haulk()
                ->table('histories')
                ->where('model_type', 'App\Models\BodyShop\Inventories\Inventory')
                ->get()
                ->toArray();

            $transactions = DbConnections::haulk()
                ->table('bs_inventory_transactions')
                ->whereNull('order_id')
                ->get()
                ->toArray();

//             dd($histories);

            foreach ($data as $k => $item){
                if(!Inventory::query()->where('origin_id', $item->id)->exists()){

                    $i = new Inventory();
                    $i->name = $item->name;
                    $i->slug = $this->slug(Str::slug($item->name));
                    $i->stock_number = $item->stock_number;
                    $i->price_retail = $item->price_retail;
                    $i->quantity = $item->quantity;
                    $i->notes = $item->notes;
                    $i->created_at = $item->created_at;
                    $i->updated_at = $item->updated_at;
                    $i->min_limit = $item->min_limit;
                    $i->deleted_at = $item->deleted_at;
                    $i->for_shop = $item->for_sale;
                    $i->length = $item->length;
                    $i->width = $item->width;
                    $i->height = $item->height;
                    $i->weight = $item->weight;
                    $i->min_limit_price = $item->min_limit_price;
                    $i->unit_id = $item->unit_id;
                    $i->origin_id = $item->id;
                    $i->category_id = $item->category_id
                        ? $category[$item->category_id]
                        : null
                    ;
                    $i->supplier_id = $item->supplier_id
                        ? $supplier[$item->supplier_id]
                        : null
                    ;

                    $i->save();

                    $seo = new Seo();
                    $seo->model_type = Inventory::MORPH_NAME;
                    $seo->model_id = $i->id;
                    $seo->save();

                    foreach ($histories as $history){
                        if($history->model_id == $item->id){
                            $h = new History();
                            $h->model_type = Inventory::MORPH_NAME;
                            $h->model_id = $i->id;
                            $h->type = $history->type == 1
                                ? HistoryType::CHANGES
                                : HistoryType::ACTIVITY
                            ;

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
                    }

                    foreach ($transactions as $transaction){
                        if($transaction->inventory_id == $item->id){
                            $t = new Transaction();
                            $t->inventory_id = $i->id;
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
            $slug .= '-' .random_int(1, 2000);
            self::slug($slug);
        }

        return $slug;
    }

    protected function fetchUnit(): void
    {
        echo "[x] START... fetch inventory unit" . PHP_EOL;

        try {
            $data = DbConnections::haulk()
                ->table('bs_inventory_units')
                ->get()
                ->toArray()
            ;

            $progressBar = new ProgressBar($this->output, count($data));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($data as $k => $item){
                $item = std_to_array($item);
                $data[$k] = $item;
            }

            $progressBar->advance();

            \DB::table(Unit::TABLE)->upsert($data, ['id']);

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch fetch inventory unit" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }

    protected function fetchCategory(): void
    {
        echo "[x] START... fetch inventory category" . PHP_EOL;

        try {
            $data = DbConnections::haulk()
                ->table('bs_inventory_categories')
                ->get()
                ->toArray()
            ;

            $parentCategory = Category::query()->where('slug', '=','old-bodyshop')->first();

            $progressBar = new ProgressBar($this->output, count($data));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($data as $k => $item){
                if(!Category::query()->where('origin_id', $item->id)->exists()){
                    $c = new Category();
                    $c->name = $item->name;
                    $c->slug = $this->slugCategory(Str::slug($item->name));
                    $c->parent_id = $parentCategory ? $parentCategory->id : 1;
                    $c->origin_id = $item->id;
                    $c->save();

                    $seo = new Seo();
                    $seo->model_id = $c->id;
                    $seo->model_type = Category::MORPH_NAME;
                    $seo->save();

                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch fetch inventory category" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
    protected function slugCategory($slug)
    {
        if(Category::query()->where('slug', $slug)->exists()){
            $slug .= '-' .random_int(1, 2000);
            self::slug($slug);
        }

        return $slug;
    }
}

