<?php

namespace Tests\Feature\Mutations\Media;

use App\Models\Catalogs\Car\Brand;
use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class StoreOneImageTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    /** @test */
    public function store_image()
    {
        $model = Brand::query()->orderBy(\DB::raw('RAND()'))->first();

        $this->assertNull($model->image);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($image: [Upload]) {imageStore(model: '. $this->image_model_brand .', modelId: ' . $model->id . ', image: $image) {id, basename, hash, position, mime, sizes, url}}"}',
                'map' => '{ "image": ["variables.image"] }',
                'image' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500)
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.imageStore');

        $model->refresh();
        $this->assertNotNull($model->image);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('basename', $responseData);
        $this->assertArrayHasKey('hash', $responseData);
        $this->assertArrayHasKey('position', $responseData);
        $this->assertArrayHasKey('mime', $responseData);
        $this->assertArrayHasKey('url', $responseData);

        // загружаем еще раз и проверяем что предыдущий файл удален
        $imageName = $model->image->hash;

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($image: [Upload]) {imageStore(model: '. $this->image_model_brand .', modelId: ' . $model->id . ', image: $image) {id, basename, hash, position, mime, sizes, url}}"}',
                'map' => '{ "image": ["variables.image"] }',
                'image' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500)
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $model->refresh();
        $this->assertNotEquals($imageName, $model->image->hash);

        // удаляем создавшиеся файлы
        app(UploadService::class)->removeImage($model->image);
    }
}
