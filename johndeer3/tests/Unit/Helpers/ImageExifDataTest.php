<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ImageExifData;
use Illuminate\Support\Facades\Storage;
use PNGMetadata\PNGMetadata;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class ImageExifDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_png_file(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image("test.png");
        $data = new ImageExifData($file);

        $this->assertTrue($data->getMetaData() instanceof PNGMetadata);
        $this->assertNull($data->getLat());
        $this->assertNull($data->getLon());
        $this->assertNull($data->getDateCreatePhoto());

        $this->assertEquals($data->getMetaData()->toArray()['IHDR']['ColorType'], 'RGB');
    }

    /** @test */
    public function success_jpg_file(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image("test.jpg");
        $data = new ImageExifData($file);

        $this->assertIsArray($data->getMetaData());
        $this->assertNotEmpty($data->getMetaData());
        $this->assertNull($data->getLat());
        $this->assertNull($data->getLon());
        $this->assertNull($data->getDateCreatePhoto());

        $this->assertEquals($data->getMetaData()['MimeType'], 'image/jpeg');
    }

    /** @test */
    public function fail_gif_file(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image("test.gif");
        $data = new ImageExifData($file);

        $this->assertNull($data->getMetaData());
        $this->assertNull($data->getLat());
        $this->assertNull($data->getLon());
        $this->assertNull($data->getDateCreatePhoto());
    }

    /** @test */
    public function success_check_data_for_test_file(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image("test.jpg");

        $data = new ImageExifData($file, true);

        $this->assertEquals($data->getLat(), 46.63722222222222);
        $this->assertEquals($data->getLon(), 32.612500000000004);
        $this->assertEquals($data->getDateCreatePhoto(), '2021:02:16 10:17:19');
    }
}

