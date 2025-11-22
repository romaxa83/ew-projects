<?php

namespace App\Console\Commands\Helpers\Order;

use Illuminate\Console\Command;

class OrderDocumentDelete extends Command
{
    protected $signature = 'es:order_document_delete';

    public function handle()
    {
        $id = $this->ask('Enter ID ');

        \App\Documents\OrderDocument::query()->delete($id);
    }
}
