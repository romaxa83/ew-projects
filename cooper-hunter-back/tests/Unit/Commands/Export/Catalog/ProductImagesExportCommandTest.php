<?php

namespace Tests\Unit\Commands\Export\Catalog;

use App\Console\Commands\Export\Catalog\ProductImagesExport;
use App\Models\Catalog\Products\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;

class ProductImagesExportCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_export(): void
    {
        Storage::fake();

        Product::factory()
            ->times(10)
            ->create()
            ->each(
            /**
             * @throws FileDoesNotExist
             * @throws FileIsTooBig
             */
                static function (Product $p, int $key) {
                    $p->addMedia(
                        UploadedFile::fake()->image('test-' . $key . '.png')
                    )->toMediaCollection(Product::MEDIA_COLLECTION_NAME);

                    $p->addMedia(
                        UploadedFile::fake()->image('test-' . $key . '-2.png')
                    )->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
                }
            );

        $this->artisan(ProductImagesExport::class, ['name' => 'test']);

        self::assertFileExists(storage_path('app/public/exports/products/test-1.xlsx'));
    }
}
