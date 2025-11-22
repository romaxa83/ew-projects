<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Upload;

use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    protected array $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);

        $this->data = [
            'image' => UploadedFile::fake()->image('image.png'),
        ];
    }

    /** @test */
    public function success_upload_to_gallery_new()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $data = $this->data;

        $this->assertCount(0, $model->getGallery());

        $this->postJson(route('api.v1.inventories.upload-image-gallery', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(1, 'data.gallery')
        ;

        $model->refresh();

        $this->assertCount(1, $model->getGallery());
    }

    /** @test */
    public function success_upload_to_gallery_add()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $img = UploadedFile::fake()->image('img_1.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->gallery($img)->create();

        $data = $this->data;

        $this->assertCount(1, $model->getGallery());

        $this->postJson(route('api.v1.inventories.upload-image-gallery', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(2, 'data.gallery')
        ;

        $model->refresh();

        $this->assertCount(2, $model->getGallery());
    }

    /** @test */
    public function fail_not_found()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.upload-image-gallery', [
            'id' => 0,
        ]), $data)
        ;

        self::assertErrorMsg($res,  __("exceptions.inventories.inventory.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsMechanic();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.upload-image-gallery', [
            'id' => $model->id,
        ]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.upload-image-gallery', [
            'id' => $model->id,
        ]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}

