<?php

namespace Tests\Feature\Mutations\Media;

use App\DTO\Media\ImageDTO;
use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class RemoveImageTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function remove_images_part_success()
    {
        $service = app(UploadService::class);
        $admin = $this->adminBuilder()->create();

        $data = [
            'model' => 'admin',
            'modelId' => $admin->id,
            'images' => [
                UploadedFile::fake()->image('first.jpg', 1980, 1240)->size(500),
                UploadedFile::fake()->image('second.jpg', 1980, 1240)->size(500),
                UploadedFile::fake()->image('third.jpg', 1980, 1240)->size(500)
            ],
        ];
        $dto = ImageDTO::byArgs($data);
        $service->uploadImages($dto);

        $admin->refresh();

        $this->assertCount(3, $admin->images);

        $removeIds = [
            $admin->images[0]['id'],
            $admin->images[1]['id']
        ];

        $response = $this->graphQL($this->getQueryStr($removeIds))
            ->assertOk();
        $responseData = $response->json('data.imagesDelete');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.images remove success'));

        $admin->refresh();

        $this->assertCount(1, $admin->images);

        // удаляем создавшиеся файлы
        app(UploadService::class)->removeAllImageAtModel($admin);
    }

    /** @test */
    public function remove_images_all_success()
    {
        $service = app(UploadService::class);
        $admin = $this->adminBuilder()->create();

        $data = [
            'model' => 'admin',
            'modelId' => $admin->id,
            'images' => [
                UploadedFile::fake()->image('first.jpg', 1980, 1240)->size(500),
                UploadedFile::fake()->image('second.jpg', 1980, 1240)->size(500)
            ],
        ];
        $dto = ImageDTO::byArgs($data);
        $service->uploadImages($dto);

        $admin->refresh();

        $this->assertCount(2, $admin->images);

        $removeIds = [
            $admin->images[0]['id'],
            $admin->images[1]['id']
        ];

        $response = $this->graphQL($this->getQueryStr($removeIds))
            ->assertOk();
        $responseData = $response->json('data.imagesDelete');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.images remove success'));

        $admin->refresh();

        $this->assertEmpty($admin->images);

        // удаляем создавшиеся файлы
        app(UploadService::class)->removeAllImageAtModel($admin);
    }

    public static function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                imagesDelete(ids: [%s,%s]) {
                    status
                    message
                }
            }',
            $data[0],
            $data[1]
        );
    }

    public static function getQueryStrOneModel(string $id): string
    {
        return sprintf('
            mutation {
                imagesDelete(ids: [%s]) {
                    status
                    message
                }
            }',
            $id
        );
    }
}
