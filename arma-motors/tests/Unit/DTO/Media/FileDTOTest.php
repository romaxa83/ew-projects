<?php

namespace Tests\Unit\DTO\Media;

use App\DTO\Media\FileDTO;
use App\Models\User\Car;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FileDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'model' => 'car',
            'type' => 'insurance',
            'modelId' => 1,
            'files' => [
                UploadedFile::fake()->image('file.pdf')->size(500),
                UploadedFile::fake()->image('file.pdf')->size(500)
            ],
        ];

        $dto = FileDTO::byArgs($data);

        $this->assertEquals($dto->getModel() , $data['model']);
        $this->assertEquals($dto->getType() , $data['type']);
        $this->assertEquals($dto->getModelId() , $data['modelId']);
        $this->assertEquals($dto->getModelClass() , Car::class);
        $this->assertNotEmpty($dto->getFiles());
        $this->assertNull($dto->getFile());
        $this->assertCount(2, $dto->getFiles());
    }

    /** @test */
    public function check_one_image_by_args()
    {
        $data = [
            'model' => 'car',
            'type' => 'insurance',
            'modelId' => 1,
            'file' => [
                UploadedFile::fake()->image('file.pdf')->size(500)
            ],
        ];

        $dto = FileDTO::byArgs($data);

        $this->assertEquals($dto->getModel() , $data['model']);
        $this->assertEquals($dto->getType() , $data['type']);
        $this->assertEquals($dto->getModelId() , $data['modelId']);
        $this->assertEquals($dto->getModelClass() , Car::class);
        $this->assertEmpty($dto->getFiles());
        $this->assertNotNull($dto->getFile());
    }

    /** @test */
    public function not_valid_model()
    {
        $data = [
            'model' => 'not_valid',
            'modelId' => 1,
            'images' => [
                UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500)
            ],
        ];

        $this->expectException(\InvalidArgumentException::class);

        FileDTO::byArgs($data);
    }
}


