<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Customers\Customer;
use App\Models\Suppliers\Supplier;
use App\Models\Suppliers\SupplierContact;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchSupplier extends BaseCommand
{
    protected $signature = 'sync:bs_supplier';

    public function exec(): void
    {
        echo "[x] START... fetch suppliers" . PHP_EOL;

        try {
            $suppliers = DbConnections::haulk()
                ->table('bs_suppliers')
                ->get()
                ->toArray()
            ;
            $suppliersContact = DbConnections::haulk()
                ->table('bs_supplier_contacts')
                ->get()
                ->toArray()
            ;

            $progressBar = new ProgressBar($this->output, count($suppliers));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($suppliers as $supplier){
                if(!Supplier::query()->where('origin_id', $supplier->id)->exists()){
                    $model = new Supplier();
                    $model->name = $supplier->name;
                    $model->url = $supplier->url;
                    $model->origin_id = $supplier->id;
                    $model->created_at = $supplier->created_at;
                    $model->updated_at = $supplier->updated_at;
                    $model->save();

                    foreach ($suppliersContact as $contact){
                        if($contact->supplier_id == $supplier->id){
                            $c = new SupplierContact();
                            $c->name = $contact->name;
                            $c->phone = $contact->phone;
                            $c->phones = $contact->phones
                                ? json_to_array($contact->phones)
                                : []
                            ;
                            $c->phone_extension = $contact->phone_extension;
                            $c->email = $contact->email;
                            $c->emails = $contact->emails
                                ? json_to_array($contact->emails)
                                : []
                            ;
                            $c->position = $contact->position;
                            $c->is_main = $contact->is_main;
                            $c->supplier_id = $model->id;
                            $c->save();
                        }
                    }

                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch suppliers" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
