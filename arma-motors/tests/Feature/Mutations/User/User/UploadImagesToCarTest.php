<?php

namespace Tests\Feature\Mutations\User\User;

use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Brand;
use App\Models\Dealership\Dealership;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Services\Media\UploadService;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class UploadImagesToCarTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function upload_images()
    {
        $user = $this->userBuilder()->withRandomCar()->create();

        $this->assertEmpty($user->cars[0]->images);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {imagesStore(model: '. $this->image_model_car .', modelId: ' . $user->cars[0]->id . ', images: $images) {id, basename, hash, position, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.imagesStore');

        $user->refresh();

        $this->assertCount(2, $responseData);
        $this->assertNotEmpty($user->cars[0]->images);
        $this->assertCount(2, $user->cars[0]->images);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('basename', $responseData[0]);
        $this->assertArrayHasKey('hash', $responseData[0]);
        $this->assertArrayHasKey('position', $responseData[0]);
        $this->assertArrayHasKey('mime', $responseData[0]);
        $this->assertArrayHasKey('url', $responseData[0]);

        // удаляем создавшиеся файлы
        app(UploadService::class)->removeAllImageAtModel($user->cars[0]);

        $user->refresh();

        $this->assertEmpty($user->cars[0]->images);
    }
}



