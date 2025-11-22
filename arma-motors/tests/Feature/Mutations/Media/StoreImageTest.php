<?php

namespace Tests\Feature\Mutations\Media;

use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class StoreImageTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    /** @test */
    public function store_images()
    {
        $admin = $this->adminBuilder()->create();

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {imagesStore(model: '. $this->image_model_admin .', modelId: ' . $admin->id . ', images: $images) {id, basename, hash, position, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.imagesStore');

        $this->assertCount(3, $responseData);
        $this->assertCount(3, $admin->images);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('basename', $responseData[0]);
        $this->assertArrayHasKey('hash', $responseData[0]);
        $this->assertArrayHasKey('position', $responseData[0]);
        $this->assertArrayHasKey('mime', $responseData[0]);
        $this->assertArrayHasKey('url', $responseData[0]);

        // удаляем создавшиеся файлы
        app(UploadService::class)->removeAllImageAtModel($admin);
    }
}
