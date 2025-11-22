<?php

namespace Tests\Feature\Mutations\Dealership;

use App\Events\ChangeHashEvent;
use App\Models\Dealership\Dealership;
use App\Models\Hash;
use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class UploadImagesTest extends TestCase
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
        \Event::fake([ChangeHashEvent::class]);

        $this->adminBuilder()->create();

        $model = Dealership::query()->orderBy(\DB::raw('RAND()'))->first();

        $this->assertEmpty($model->images);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {imagesStore(model: '. $this->image_model_dealership .', modelId: ' . $model->id . ', images: $images) {id, basename, hash, position, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.imagesStore');

        $model->refresh();

        $this->assertCount(2, $responseData);
        $this->assertNotEmpty($model->images);
        $this->assertCount(2, $model->images);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('basename', $responseData[0]);
        $this->assertArrayHasKey('hash', $responseData[0]);
        $this->assertArrayHasKey('position', $responseData[0]);
        $this->assertArrayHasKey('mime', $responseData[0]);
        $this->assertArrayHasKey('url', $responseData[0]);

        \Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_DEALERSHIP;
        });

        // удаляем создавшиеся файлы
        app(UploadService::class)->removeAllImageAtModel($model);

        $model->refresh();

        $this->assertEmpty($model->images);
    }
}


