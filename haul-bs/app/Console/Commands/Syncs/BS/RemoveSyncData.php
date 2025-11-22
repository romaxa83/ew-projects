<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\Localization\Models\Translation;
use App\Foundations\Modules\Location\Models\City;
use App\Foundations\Modules\Location\Models\State;
use App\Foundations\Modules\Media\Models\Media;
use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Companies\Company;
use App\Models\Customers\Customer;
use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Inventories\Unit;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\Payment;
use App\Models\Orders\BS\TypeOfWork;
use App\Models\Orders\BS\TypeOfWorkInventory;
use App\Models\Settings\Settings;
use App\Models\Suppliers\Supplier;
use App\Models\Suppliers\SupplierContact;
use App\Models\Tags\Tag;
use App\Models\Tags\Taggable;
use App\Models\Vehicles\Make;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;

class RemoveSyncData extends BaseCommand
{
    protected $signature = 'sync:bs_data_remove {table?}';

    protected $haulk_tables = [
        Tag::TABLE,
        Customer::TABLE,
        Translation::TABLE,
        Settings::TABLE,
        State::TABLE,
        Make::TABLE,
        Truck::TABLE,
        Trailer::TABLE,
        Supplier::TABLE,
        Inventory::TABLE,
        Order::TABLE,
    ];

    public function exec(): void
    {
        $table = $this->argument('table');

        if($table){
            if(in_array($table, $this->haulk_tables)){
                $this->remove($table);
            } else {
                throw new \Exception("not support this table [$table]");
            }
        } else {
            foreach ($this->haulk_tables as $table){
                $this->remove($table);
            }
        }

    }

    private function remove(string $table): void
    {
        if($table == Customer::TABLE){
            \DB::table($table)->delete();
            \DB::table(Comment::TABLE)
                ->where('model_type', Customer::MORPH_NAME)
                ->delete();
            \DB::table(Taggable::TABLE)
                ->where('taggable_type', Customer::MORPH_NAME)
                ->delete();
            \DB::table(Media::TABLE)
                ->where('model_type', Customer::MORPH_NAME)
                ->delete();
        } elseif ($table == State::TABLE){
            \DB::table(City::TABLE)->delete();
            \DB::table($table)->delete();
        } elseif ($table == Settings::TABLE) {
            \DB::table($table)->delete();
            \DB::table(Media::TABLE)
                ->where('model_type', Settings::MORPH_NAME)
                ->delete();
        } elseif ($table == Truck::TABLE){
            \DB::table($table)->delete();
            \DB::table(Company::TABLE)->delete();
            \DB::table(Comment::TABLE)
                ->where('model_type', Truck::MORPH_NAME)
                ->delete();
            \DB::table(Taggable::TABLE)
                ->where('taggable_type', Truck::MORPH_NAME)
                ->delete();
            \DB::table(History::TABLE)
                ->where('model_type', Truck::MORPH_NAME)
                ->delete();
            \DB::table(Media::TABLE)
                ->where('model_type', Truck::MORPH_NAME)
                ->delete();
            \DB::table(Customer::TABLE)
                ->where('from_haulk', true)
                ->delete();
        } elseif ($table == Trailer::TABLE) {
            \DB::table($table)->delete();
            \DB::table(Company::TABLE)->delete();
            \DB::table(Comment::TABLE)
                ->where('model_type', Trailer::MORPH_NAME)
                ->delete();
            \DB::table(Taggable::TABLE)
                ->where('taggable_type', Trailer::MORPH_NAME)
                ->delete();
            \DB::table(History::TABLE)
                ->where('model_type', Trailer::MORPH_NAME)
                ->delete();
            \DB::table(Media::TABLE)
                ->where('model_type', Trailer::MORPH_NAME)
                ->delete();
        } elseif($table == Supplier::TABLE){
            \DB::table($table)->delete();
            \DB::table(SupplierContact::TABLE)->delete();
        } elseif ($table == Inventory::TABLE){
            \DB::table($table)->whereNotNull('origin_id')->delete();
            \DB::table(Category::TABLE)
                ->whereNotNull('origin_id')
                ->delete();
            \DB::table(Seo::TABLE)
                ->where('model_type', Category::MORPH_NAME)
                ->delete();
            \DB::table(Seo::TABLE)
                ->where('model_type', Inventory::MORPH_NAME)
                ->delete();
            \DB::table(History::TABLE)
                ->where('model_type', Inventory::MORPH_NAME)
                ->delete();
            \DB::table(Transaction::TABLE)
                ->delete();
//            \DB::table(Unit::TABLE)->delete();
        } elseif($table == Order::TABLE){
            \DB::table($table)->delete();
            \DB::table(Transaction::TABLE)
                ->whereNotNull('order_id')
                ->delete();
            \DB::table(Media::TABLE)
                ->where('model_type', Order::MORPH_NAME)
                ->delete();
            \DB::table(Payment::TABLE)->delete();
            \DB::table(History::TABLE)
                ->where('model_type', Inventory::MORPH_NAME)
                ->delete();
            \DB::table(TypeOfWork::TABLE)->delete();
            \DB::table(TypeOfWorkInventory::TABLE)->delete();
        } else {
            \DB::table($table)->delete();
        }

        $this->info('TRUNCATE - '. $table);
    }
}
