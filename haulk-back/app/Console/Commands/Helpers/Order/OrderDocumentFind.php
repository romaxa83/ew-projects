<?php

namespace App\Console\Commands\Helpers\Order;

use Illuminate\Console\Command;

class OrderDocumentFind extends Command
{
    protected $signature = 'es:order_document_find';

    public function handle()
    {
        $id = $this->ask('Enter ID ');

        $docs = \App\Documents\OrderDocument::find($id);
//        $docs = \App\Documents\OrderDocument::query()->all();

        dd($docs);

    }
}


