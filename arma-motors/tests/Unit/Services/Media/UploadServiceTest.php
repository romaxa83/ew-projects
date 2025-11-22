<?php

namespace Tests\Unit\Services\Media;

use App\DTO\Media\ImageDTO;
use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Unit\DTO\Media\ImageDTOTest;

class UploadServiceTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function check_create_and_delete()
    {
        $dataSize = ImageDTOTest::dataSize();
        \Config::set('image.models.admin.sizes', $dataSize);

        $admin = $this->adminBuilder()->create();

        $service = app(UploadService::class);

        $this->assertEmpty($admin->images);

        $data = [
            'model' => 'admin',
            'modelId' => $admin->id,
            'images' => [
                UploadedFile::fake()->image('file.jpg', 1000, 700)->size(500),
                UploadedFile::fake()->image('file.jpg', 1000, 700)->size(500)
            ],
        ];

        $dto = ImageDTO::byArgs($data);

        $service->uploadImages($dto);

        $admin->refresh();

        $this->assertNotEmpty($admin->images);

        $pathToFile = [];
        foreach ($admin->images as $img){
            // проверяем наличие оригинального файла
            $this->assertTrue(file_exists($img->pathToFileStorage()));
            $pathToFile[] = $img->pathToFileStorage();
            foreach ($dataSize as $key => $item){
                $this->assertTrue(file_exists($img->pathToFileStorage($key)));
                $pathToFile[] = $img->pathToFileStorage($key);
            }
        }

        $service->removeAllImageAtModel($admin);

        $admin->refresh();
        $this->assertEmpty($admin->images);

        $pathToFile = [];
        foreach ($pathToFile as $path){
            $this->assertFalse(file_exists($path));
        }
    }
}
