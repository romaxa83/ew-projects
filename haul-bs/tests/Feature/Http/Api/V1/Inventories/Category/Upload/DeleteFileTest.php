<?php

namespace Feature\Http\Api\V1\Inventories\Category\Upload;

use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\TestCase;

class DeleteFileTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryBuilder = resolve(CategoryBuilder::class);
    }

    /** @test */
    public function success_delete_menu_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $header_img = UploadedFile::fake()->image('header.png');
        $menu_img = UploadedFile::fake()->image('menu.png');

        /** @var $model Category */
        $model = $this->categoryBuilder
            ->menuImg($menu_img)
            ->headerImg($header_img)
            ->create();

        $this->assertNotNUll($model->getMenuImg());
        $this->assertNotNUll($model->getHeaderImg());

        $this->deleteJson(route('api.v1.inventories.category.delete-file', [
            'id' => $model->id,
            'imageId' => $model->getMenuImg()->id
        ]))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertNUll($model->getMenuImg());
        $this->assertNotNUll($model->getHeaderImg());
    }

    /** @test */
    public function fail_not_found()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $header_img = UploadedFile::fake()->image('header.png');

        /** @var $model Category */
        $model = $this->categoryBuilder
            ->headerImg($header_img)
            ->create();

        $res = $this->deleteJson(route('api.v1.inventories.category.delete-file', [
            'id' => 0,
            'imageId' => $model->getHeaderImg()->id
        ]))
        ;

        self::assertErrorMsg($res,  __("exceptions.inventories.category.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $header_img = UploadedFile::fake()->image('header.png');

        /** @var $model Category */
        $model = $this->categoryBuilder
            ->headerImg($header_img)
            ->create();

        $res = $this->deleteJson(route('api.v1.inventories.category.delete-file', [
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

        $menu_img = UploadedFile::fake()->image('menu.png');

        /** @var $model Category */
        $model = $this->categoryBuilder
            ->menuImg($menu_img)
            ->create();

        $res = $this->deleteJson(route('api.v1.inventories.category.delete-file', [
            'id' => $model->id,
            'imageId' => $model->getMenuImg()->id
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $menu_img = UploadedFile::fake()->image('menu.png');

        /** @var $model Category */
        $model = $this->categoryBuilder
            ->menuImg($menu_img)
            ->create();

        $res = $this->deleteJson(route('api.v1.inventories.category.delete-file', [
            'id' => $model->id,
            'imageId' => $model->getMenuImg()->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
