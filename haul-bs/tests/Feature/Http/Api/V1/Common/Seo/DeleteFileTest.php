<?php

namespace Tests\Feature\Http\Api\V1\Common\Seo;

use App\Foundations\Modules\Seo\Models\Seo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class DeleteFileTest extends TestCase
{
    use DatabaseTransactions;

    protected SeoBuilder $seoBuilder;
    protected BrandBuilder $brandBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->seoBuilder = resolve(SeoBuilder::class);
        $this->brandBuilder = resolve(BrandBuilder::class);
    }

    /** @test */
    public function success_delete_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $seo_img = UploadedFile::fake()->image('seo.png');

        $brand = $this->brandBuilder->create();

        /** @var $model Seo */
        $model = $this->seoBuilder
            ->model($brand)
            ->image($seo_img)
            ->create();

        $this->assertNotNull($model->getFirstImage());

        $this->deleteJson(route('api.v1.seo.delete-file', [
            'id' => $model->id,
            'imageId' => $model->getFirstImage()->id
        ]))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertNull($model->getFirstImage());
    }

    /** @test */
    public function fail_not_found()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $brand = $this->brandBuilder->create();

        $seo_img = UploadedFile::fake()->image('seo.png');

        /** @var $model Seo */
        $model = $this->seoBuilder
            ->model($brand)
            ->image($seo_img)
            ->create();

        $res = $this->deleteJson(route('api.v1.seo.delete-file', [
            'id' => 0,
            'imageId' => $model->getFirstImage()->id
        ]))
        ;

        self::assertErrorMsg($res,  __("exceptions.seo.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $seo_img = UploadedFile::fake()->image('seo.png');

        $brand = $this->brandBuilder->create();

        /** @var $model Seo */
        $model = $this->seoBuilder
            ->model($brand)
            ->image($seo_img)
            ->create();

        $res = $this->deleteJson(route('api.v1.seo.delete-file', [
            'id' => $model->id,
            'imageId' => 0
        ]))
        ;

        self::assertErrorMsg($res,  __('exceptions.file.not_found'), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_auth()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $seo_img = UploadedFile::fake()->image('seo.png');

        $brand = $this->brandBuilder->create();

        /** @var $model Seo */
        $model = $this->seoBuilder
            ->model($brand)
            ->image($seo_img)
            ->create();

        $res = $this->deleteJson(route('api.v1.seo.delete-file', [
            'id' => $model->id,
            'imageId' => $model->getFirstImage()->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
