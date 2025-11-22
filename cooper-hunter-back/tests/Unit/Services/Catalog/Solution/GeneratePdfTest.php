<?php


namespace Tests\Unit\Services\Catalog\Solution;


use App\Dto\Utilities\Pdf\PdfDataDto;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Solution;
use App\Services\Catalog\Solutions\SolutionService;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class GeneratePdfTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public function test_check_pdf_data(): void
    {
        Config::set('app.env', 'local');
        $this->seed(SolutionDemoSeeder::class);

        $outdoor = Solution::query()
            ->where('type', SolutionTypeEnum::OUTDOOR)
            ->where('zone', SolutionZoneEnum::MULTI)
            ->where('btu', 48000)
            ->first();
        $outdoor->product->clearMediaCollection(Product::MEDIA_COLLECTION_NAME);

        $outdoor
            ->product
            ->addMedia(
                $file = UploadedFile::fake()
                    ->createWithContent(
                        'file.jpg',
                        file_get_contents(storage_path('testing/TEST-PHOTO.jpg'))
                    )
            )
            ->toMediaCollection(Product::MEDIA_COLLECTION_NAME);

        $service = resolve(SolutionService::class);

        $service->download(
            [
                'outdoor_id' => $outdoor->id,
                'indoors' => [
                    [
                        'indoor_id' => $outdoor->children[1]->id,
                        'line_set_id' => $outdoor->children[1]->children[0]->id,
                    ],
                    [
                        'indoor_id' => $outdoor->children[0]->id,
                        'line_set_id' => $outdoor->children[0]->children[0]->id,
                    ],
                    [
                        'indoor_id' => $outdoor->children[1]->id,
                        'line_set_id' => $outdoor->children[1]->children[0]->id,
                    ],
                ]
            ]
        );

        $hash = $service->getPdfDataHash();

        $this->assertNotNull($hash);

        $pdfData = $service->getPdfDataFromCache($hash);

        $this->assertNotNull($pdfData);
        $this->assertInstanceOf(PdfDataDto::class, $pdfData);

        $pdfData = $pdfData->getPdfData()
            ->toArray();

        $this->assertArrayHasKey('category', $pdfData);
        $this->assertArrayHasKey('outdoor', $pdfData);
        $this->assertArrayHasKey('indoors', $pdfData);
        $this->assertCount(3, $pdfData['indoors']);
    }
}
