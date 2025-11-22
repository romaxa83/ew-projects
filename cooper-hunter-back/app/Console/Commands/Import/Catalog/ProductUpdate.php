<?php

namespace App\Console\Commands\Import\Catalog;

use App\Models\Catalog\Products\Product;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Illuminate\Console\Command;

class ProductUpdate extends Command
{
    protected $signature = 'import:product {name=_rebates}';

    protected $description = 'Импортирует товары, для обновления (unitType)';

    /**
     * @throws UnsupportedTypeException
     * @throws ReaderNotOpenedException
     * @throws IOException
     */
    public function handle()
    {
        $file = database_path("files/{$this->argument('name')}.xlsx");
        if(!file_exists($file)){
            $this->warn("Not found file - [{$file}]");
            return false;
        }

        $data = fastexcel()
            ->withoutHeaders()
            ->import(
                $file,
                fn(array $row) => [
                    'id' => $row[1],
                    'show_rebate' => $row[4],
                ],
            );

        $count = 0;
        foreach ($data as $k => $item){
            if($k != 0){
                $model = Product::query()->where('guid', $item['id'])->first();
                if($model){
                    $model->update([
                        'show_rebate' => $item['show_rebate']
                    ]);
                    $count++;
                }
            }
        }

        $this->info("Update [{$count}] product");
    }
}
