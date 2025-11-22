<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchInventoryTransactions extends BaseCommand
{
    protected $signature = 'sync:bs_inventory_transactions';

    public function exec() :void
    {
        $this->fetchInventoriesData();
    }
    protected function fetchInventoriesData(): void
    {
        echo "[x] START... fetch histories" . PHP_EOL;

        $inventories = Inventory::query()->whereNotNull('origin_id')->get();

        $progressBar = new ProgressBar($this->output, count($inventories));
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($inventories as $inventory) {
            $progressBar->advance();

            /** @var Inventory $inventory */
            Transaction::query()
                ->where('inventory_id', $inventory->id)
                ->delete();

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
        $progressBar->finish();
    }
}
