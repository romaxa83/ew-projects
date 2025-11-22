<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Upload;

use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class DeleteFileTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_delete_from_gallery()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $file_1 = UploadedFile::fake()->image('file_1.png');
        $file_2 = UploadedFile::fake()->image('file_1.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->gallery($file_1, $file_2)
            ->create();

        $this->assertCount(2, $model->getGallery());

        $this->deleteJson(route('api.v1.inventories.delete-file', [
            'id' => $model->id,
            'imageId' => $model->getGallery()[0]->id
        ]))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertCount(1, $model->getGallery());
    }

    /** @test */
    public function fail_not_found()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $file_1 = UploadedFile::fake()->image('file_1.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->gallery($file_1)
            ->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete-file', [
            'id' => 0,
            'imageId' => $model->getGallery()[0]->id
        ]))
        ;

        self::assertErrorMsg($res,  __("exceptions.inventories.inventory.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->inventoryBuilder
            ->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete-file', [
            'id' => $model->id,
            'imageId' => 0
        ]))
        ;

        self::assertErrorMsg($res,  __('exceptions.file.not_found'), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsMechanic();

        $file_1 = UploadedFile::fake()->image('file_1.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->gallery($file_1)
            ->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete-file', [
            'id' => $model->id,
            'imageId' => $model->getGallery()[0]->id
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $file_1 = UploadedFile::fake()->image('file_1.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->gallery($file_1)
            ->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete-file', [
            'id' => $model->id,
            'imageId' => $model->getGallery()[0]->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
