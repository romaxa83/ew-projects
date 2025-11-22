<?php

namespace Tests\Feature\Mutations\Catalog\Car;

use App\Models\Catalogs\Car\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Mutations\Media\RemoveImageTest;
use Tests\Feature\Queries\Catalog\Car\GetOneBrandTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class BrandUploadImagesTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function upload_images()
    {
        $this->adminBuilder()->create();

        $model = Brand::query()->orderBy(\DB::raw('RAND()'))->first();

        $this->assertEmpty($model->image);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {imagesStore(model: '. $this->image_model_brand .', modelId: ' . $model->id . ', images: $images) {id, basename, hash, position, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500)
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.imagesStore');

        $model->refresh();

        $this->assertNotEmpty($model->image);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('basename', $responseData[0]);
        $this->assertArrayHasKey('hash', $responseData[0]);
        $this->assertArrayHasKey('position', $responseData[0]);
        $this->assertArrayHasKey('mime', $responseData[0]);
        $this->assertArrayHasKey('url', $responseData[0]);

        // запрос на получение данного бренда, проверяем что у него есть картинка
        $responseLook = $this->graphQL(GetOneBrandTest::getQueryStr($model->id));
        $responseLookData = $responseLook->json('data.brand');

        $this->assertArrayHasKey('image', $responseLookData);
        $this->assertArrayHasKey('id', $responseLookData['image']);
        $this->assertArrayHasKey('sizes', $responseLookData['image']);
        $this->assertCount(3, $responseLookData['image']['sizes']);
        $this->assertArrayHasKey('basename', $responseData[0]);
        $this->assertEquals($model->image->id, $responseLookData['image']['id']);

        // запрос на удаление
        $responseRemove = $this->graphQL(RemoveImageTest::getQueryStrOneModel($model->image->id));

        $this->assertTrue($responseRemove->json('data.imagesDelete.status'));

        $model->refresh();
        $this->assertEmpty($model->image);
    }
}
