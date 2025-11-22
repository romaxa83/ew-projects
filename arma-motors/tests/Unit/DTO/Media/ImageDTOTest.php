<?php

namespace Tests\Unit\DTO\Media;

use App\DTO\Media\ImageDTO;
use App\Models\Admin\Admin;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'model' => 'admin',
            'type' => 'avatar',
            'modelId' => 1,
            'images' => [
                UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500)
            ],
        ];

        $dto = ImageDTO::byArgs($data);

        $this->assertEquals($dto->getModel() , $data['model']);
        $this->assertEquals($dto->getType() , $data['type']);
        $this->assertEquals($dto->getModelId() , $data['modelId']);
        $this->assertEquals($dto->getModelClass() , Admin::class);
        $this->assertNotEmpty($dto->getImages());
        $this->assertNull($dto->getImage());
        $this->assertNotEmpty($dto->getSizes());
        $this->assertCount(2, $dto->getImages());
    }

    /** @test */
    public function check_one_image_by_args()
    {
        $data = [
            'model' => 'admin',
            'type' => 'avatar',
            'modelId' => 1,
            'image' => [
                UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500)
            ],
        ];

        $dto = ImageDTO::byArgs($data);

        $this->assertEquals($dto->getModel() , $data['model']);
        $this->assertEquals($dto->getType() , $data['type']);
        $this->assertEquals($dto->getModelId() , $data['modelId']);
        $this->assertEquals($dto->getModelClass() , Admin::class);
        $this->assertEmpty($dto->getImages());
        $this->assertNotNull($dto->getImage());
        $this->assertNotEmpty($dto->getSizes());
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

        ImageDTO::byArgs($data);
    }

    public static function dataSize(): array
    {
        return [
            'small' => [
                'width' => 170,
                'height' => 170,
                'mode' => 'resize',
            ],
            'big' => [
                'width' => 350,
                'height' => 350,
                'mode' => 'resize',
            ]
        ];
    }
}

