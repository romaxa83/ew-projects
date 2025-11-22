<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Category;

use App\Foundations\Modules\Media\Images\ImageAbstract;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\Commands\Category\CategoryCreateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    protected CategoryBuilder $categoryBuilder;
    protected SeoBuilder $seoBuilder;

    public function setUp(): void
    {
        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $parent = $this->categoryBuilder->create();

        $header = UploadedFile::fake()->image('header.png');
        $menu = UploadedFile::fake()->image('menu.png');
        $mobile = UploadedFile::fake()->image('mobile.png');
        $seo = UploadedFile::fake()->image('seo.png');

        /** @var $model Category */
        $model = $this->categoryBuilder
            ->parent($parent)
            ->headerImg($header)
            ->menuImg($menu)
            ->mobileImg($mobile)
            ->create();

        $this->seoBuilder->model($model)->image($seo)->create();

        /** @var $command CategoryCreateCommand */
        $command = resolve(CategoryCreateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['guid'], $model->id);
        $this->assertEquals($res['slug'], $model->slug);
        $this->assertEquals($res['active'], $model->active);
        $this->assertEquals($res['display_menu'], $model->display_menu);
        $this->assertEquals($res['parent_guid'], $model->parent_id);

        $this->assertEquals($res['translations'][0]['language'], 'en');
        $this->assertEquals($res['translations'][0]['name'], $model->name);
        $this->assertEquals($res['translations'][0]['description'], $model->desc);
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
        $this->assertEquals($res['seoImage'][ImageAbstract::SIZE_ORIGINAL],  $this->fullUrl($model->seo->getFirstImage(),'original_webp'));
        $this->assertEquals($res['seoImage'][ImageAbstract::SIZE_EXTRA_LARGE],  $this->fullUrl($model->seo->getFirstImage(), ImageAbstract::SIZE_EXTRA_LARGE));
        $this->assertEquals($res['seoImage'][ImageAbstract::SIZE_EXTRA_LARGE . '_jpg'],  $this->fullUrl($model->seo->getFirstImage(), ImageAbstract::SIZE_EXTRA_LARGE . '_jpg'));

        $this->assertEquals($res['headerImage']['id'], $model->getHeaderImg()->id);
        $this->assertEquals($res['headerImage']['name'], $model->getHeaderImg()->name);
        $this->assertEquals($res['headerImage']['file_name'], $model->getHeaderImg()->file_name);
        $this->assertEquals($res['headerImage']['mime_type'], $model->getHeaderImg()->mime_type);
        $this->assertEquals($res['headerImage']['order_column'], $model->getHeaderImg()->order_column);
        $this->assertEquals($res['headerImage']['size'], $model->getHeaderImg()->size);
        $this->assertEquals($res['headerImage'][ImageAbstract::SIZE_ORIGINAL],  $this->fullUrl($model->getHeaderImg(),'original_webp'));
        $this->assertEquals($res['headerImage'][ImageAbstract::SIZE_SMALL],  $this->fullUrl($model->getHeaderImg(), ImageAbstract::SIZE_SMALL));
        $this->assertEquals($res['headerImage'][ImageAbstract::SIZE_SMALL . '_jpg'],  $this->fullUrl($model->getHeaderImg(), ImageAbstract::SIZE_SMALL. '_jpg'));

        $this->assertEquals($res['menuImage']['id'], $model->getMenuImg()->id);
        $this->assertEquals($res['menuImage']['name'], $model->getMenuImg()->name);
        $this->assertEquals($res['menuImage']['file_name'], $model->getMenuImg()->file_name);
        $this->assertEquals($res['menuImage']['mime_type'], $model->getMenuImg()->mime_type);
        $this->assertEquals($res['menuImage']['order_column'], $model->getMenuImg()->order_column);
        $this->assertEquals($res['menuImage']['size'], $model->getMenuImg()->size);
        $this->assertEquals($res['menuImage'][ImageAbstract::SIZE_ORIGINAL],  $this->fullUrl($model->getMenuImg(),'original_webp'));
        $this->assertEquals($res['menuImage'][ImageAbstract::SIZE_SMALL],  $this->fullUrl($model->getMenuImg(), ImageAbstract::SIZE_SMALL));
        $this->assertEquals($res['menuImage'][ImageAbstract::SIZE_SMALL . '_jpg'],  $this->fullUrl($model->getMenuImg(), ImageAbstract::SIZE_SMALL . '_jpg'));

        $this->assertEquals($res['mobileImage']['id'], $model->getMobileImg()->id);
        $this->assertEquals($res['mobileImage']['name'], $model->getMobileImg()->name);
        $this->assertEquals($res['mobileImage']['file_name'], $model->getMobileImg()->file_name);
        $this->assertEquals($res['mobileImage']['mime_type'], $model->getMobileImg()->mime_type);
        $this->assertEquals($res['mobileImage']['order_column'], $model->getMobileImg()->order_column);
        $this->assertEquals($res['mobileImage']['size'], $model->getMobileImg()->size);
        $this->assertEquals($res['mobileImage'][ImageAbstract::SIZE_ORIGINAL],  $this->fullUrl($model->getMobileImg(),'original_webp'));
        $this->assertEquals($res['mobileImage'][ImageAbstract::SIZE_EXTRA_LARGE],  $this->fullUrl($model->getMobileImg(), ImageAbstract::SIZE_EXTRA_LARGE));
        $this->assertEquals($res['mobileImage'][ImageAbstract::SIZE_EXTRA_LARGE . '_jpg'],  $this->fullUrl($model->getMobileImg(), ImageAbstract::SIZE_EXTRA_LARGE . '_jpg'));
    }

    /** @test */
    public function check_prepare_data_without_seo_and_img()
    {
        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        /** @var $command CategoryCreateCommand */
        $command = resolve(CategoryCreateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['guid'], $model->id);
        $this->assertEquals($res['slug'], $model->slug);
        $this->assertNull($res['parent_guid']);
        $this->assertEquals($res['translations'][0]['language'], 'en');
        $this->assertEquals($res['translations'][0]['name'], $model->name);
        $this->assertNull($res['translations'][0]['seo_h1']);
        $this->assertNull($res['translations'][0]['seo_title']);
        $this->assertNull($res['translations'][0]['seo_description']);
        $this->assertNull($res['translations'][0]['seo_text']);
        $this->assertFalse(isset($res['seoImage']));
        $this->assertFalse(isset($res['headerImage']));
        $this->assertFalse(isset($res['menuImage']));
        $this->assertFalse(isset($res['mobileImage']));
    }

    /** @test */
    public function check_uri()
    {
        /** @var $command CategoryCreateCommand */
        $command = resolve(CategoryCreateCommand::class);
        $this->assertEquals($command->getUri(), config("requests.e_com.paths.category.create"));
    }
}
