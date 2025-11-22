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
use App\Models\Inventories\Inventory;
use App\Models\Users\User;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchHistories extends BaseCommand
{

    protected $signature = 'sync:bs_histories';

    public function exec() :void
    {
        $this->fetchHistoryData();
    }
    protected function fetchHistoryData(): void
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

        echo "[x] START... fetch histories" . PHP_EOL;

        $inventories = Inventory::query()->whereNotNull('origin_id')->get();

        $progressBar = new ProgressBar($this->output, count($inventories));
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($inventories as $inventory) {
            /** @var Inventory $inventory */
            $progressBar->advance();

            History::query()
                ->where('model_type', Inventory::MORPH_NAME)
                ->where('model_id', $inventory->id)
                ->delete();

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
        }
        $progressBar->finish();
    }
}
