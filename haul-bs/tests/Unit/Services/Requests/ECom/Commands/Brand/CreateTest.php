<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Brand;

use App\Foundations\Modules\Media\Images\ImageAbstract;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Inventories\Brand;
use App\Services\Requests\ECom\Commands\Brand\BrandCreateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    protected BrandBuilder $brandBuilder;
    protected SeoBuilder $seoBuilder;

    public function setUp(): void
    {
        $this->brandBuilder = resolve(BrandBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $img = UploadedFile::fake()->image('img.png');

        $this->seoBuilder->model($model)->image($img)->create();

        /** @var $command BrandCreateCommand */
        $command = resolve(BrandCreateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['guid'], $model->id);
        $this->assertEquals($res['slug'], $model->slug);
        $this->assertEquals($res['translations'][0]['language'], 'en');
        $this->assertEquals($res['translations'][0]['name'], $model->name);
        $this->assertEquals($res['translations'][0]['seo_h1'], $model->seo->h1);
        $this->assertEquals($res['translations'][0]['seo_title'], $model->seo->title);
        $this->assertEquals($res['translations'][0]['seo_description'], $model->seo->desc);
        $this->assertEquals($res['translations'][0]['seo_text'], $model->seo->text);
        $this->assertEquals($res['seoImage']['id'], $model->seo->getFirstImage()->id);
        $this->assertEquals($res['seoImage']['name'], $model->seo->getFirstImage()->name);
        $this->assertEquals($res['seoImage']['file_name'], $model->seo->getFirstImage()->file_name);
        $this->assertEquals($res['seoImage']['mime_type'], $model->seo->getFirstImage()->mime_type);
        $this->assertEquals($res['seoImage']['order_column'], $model->seo->getFirstImage()->order_column);
        $this->assertEquals($res['seoImage']['size'], $model->seo->getFirstImage()->size);
        $this->assertEquals($res['seoImage'][ImageAbstract::SIZE_ORIGINAL],  $this->fullUrl($model->seo->getFirstImage(), 'original_webp'));
        $this->assertEquals($res['seoImage'][ImageAbstract::SIZE_EXTRA_LARGE],  $this->fullUrl($model->seo->getFirstImage(), ImageAbstract::SIZE_EXTRA_LARGE));
        $this->assertEquals($res['seoImage'][ImageAbstract::SIZE_MEDIUM],  $this->fullUrl($model->seo->getFirstImage(), ImageAbstract::SIZE_MEDIUM));
    }

    /** @test */
    public function check_prepare_data_without_seo()
    {
        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        /** @var $command BrandCreateCommand */
        $command = resolve(BrandCreateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['guid'], $model->id);
        $this->assertEquals($res['slug'], $model->slug);
        $this->assertEquals($res['translations'][0]['language'], 'en');
        $this->assertEquals($res['translations'][0]['name'], $model->name);
        $this->assertNull($res['translations'][0]['seo_h1']);
        $this->assertNull($res['translations'][0]['seo_title']);
        $this->assertNull($res['translations'][0]['seo_description']);
        $this->assertNull($res['translations'][0]['seo_text']);
        $this->assertFalse(isset($res['seoImage']));
    }

    /** @test */
    public function check_uri()
    {
        /** @var $command BrandCreateCommand */
        $command = resolve(BrandCreateCommand::class);
        $this->assertEquals($command->getUri(), config("requests.e_com.paths.brand.create"));
    }
}
