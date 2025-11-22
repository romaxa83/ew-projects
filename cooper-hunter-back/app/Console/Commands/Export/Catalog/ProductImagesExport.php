<?php

namespace App\Console\Commands\Export\Catalog;

use App\Models\Catalog\Products\Product;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductImagesExport extends Command
{
    protected $signature = 'export:product-images {name?}';

    protected $description = 'Экспорт картинок';

    public function handle(): int
    {
        Product::query()
            ->chunk(
                5000,
                fn(Collection $products, int $page) => $this->exportProductImages($products, $page)
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
    private function exportProductImages(Collection|array $products, int $page): void
    {
        $basePath = storage_path('app/public/exports/products/');

        File::ensureDirectoryExists($basePath);

        $file = $this->generateName($basePath, $page);

        $data = [];
        $images = [];

        foreach ($products as $product) {
            /**
             * @var Media $media
             */
            foreach ($product->getMedia(Product::MEDIA_COLLECTION_NAME) as $iteration => $media) {
                $images[] = [
                    'product_id' => $product->id,
                    'product_guid' => $product->guid,
                    'main' => $iteration === 0 ? 1 : 0,
                    'url' => $media->getFullUrl(),
                ];
            }

            $data[] = [
                'id' => $product->id,
                'guid' => $product->guid,
                'name' => $product->title,
                'url' => config('front_routes.product') . $product->slug
            ];
        }

        $sheets = new SheetCollection([
            'Products' => $data,
            'Images' => $images,
        ]);

        (new FastExcel($sheets))->export($file);
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
