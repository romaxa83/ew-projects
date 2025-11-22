<?php

namespace App\Console\Commands\Export\Catalog;

use App\Models\Catalog\Products\Product;
use App\Notifications\System\SendMessageWithFile;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class ProductExport extends Command
{
    protected $signature = 'export:product {name?} {--email=}';

    protected $description = 'Экспорт товаров';

    public function handle(): int
    {
        Product::query()
            ->with('unitType')
            ->chunk(
                5000,
                fn(Collection $products, int $page) => $this->exportProduct($products, $page)
            );

        return self::SUCCESS;
    }

    /**
     * @param Collection|Product[] $products
     *
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    private function exportProduct(Collection|array $products, int $page): void
    {
        $basePath = storage_path('app/public/exports/products/');

        File::ensureDirectoryExists($basePath);

        $file = $this->generateName($basePath, $page);

        $data = [];

        foreach ($products as $product) {
            /** @var $product Product */

            $data[] = [
                'id' => $product->id,
                'name' => $product->title,
                'unit type' => $product->unitType->name ?? null
            ];
        }

        $sheets = new SheetCollection([
            'Products' => $data
        ]);

        (new FastExcel($sheets))->export($file);

        if($email = $this->option('email')){
            Notification::route('mail', $email)
                ->notify(new SendMessageWithFile(
                    Storage::url('/exports/products/'.last(explode('/', $file)))
                ));

            $this->info('Send email to: ' . $email);
        }

    }

    public function generateName(string $basePath, int $page): string
    {
        $basePath = rtrim($basePath, '/');

        if ($name = $this->argument('name')) {
            $fileName = $name . '-' . $page . '.xlsx';
        } else {
            $fileName = $page . '.xlsx';
        }

        $this->info('Created file: ' . $fileName);

        return sprintf(
            '%s/%s',
            $basePath,
            $fileName,
        );
    }
}
