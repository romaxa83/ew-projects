<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Inventory;

use App\Foundations\Modules\Media\Images\ImageAbstract;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryUpdateCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\InventoryFeatureValueBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    protected InventoryBuilder $inventoryBuilder;
    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $valueBuilder;
    protected InventoryFeatureValueBuilder $inventoryFeatureValueBuilder;
    protected SeoBuilder $seoBuilder;

    public function setUp(): void
    {
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->valueBuilder = resolve(FeatureValueBuilder::class);
        $this->inventoryFeatureValueBuilder = resolve(InventoryFeatureValueBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $seo_img = UploadedFile::fake()->image('seo_img.png');
        $main_image = UploadedFile::fake()->image('main_img.png');
        $imd_1 = UploadedFile::fake()->image('img_1.png');
        $imd_2 = UploadedFile::fake()->image('img_2.png');

        $feature_1 = $this->featureBuilder->create();
        $feature_2 = $this->featureBuilder->create();

        $val_1 = $this->valueBuilder->feature($feature_1)->create();
        $val_2 = $this->valueBuilder->feature($feature_1)->create();
        $val_3 = $this->valueBuilder->feature($feature_1)->create();
        $val_4 = $this->valueBuilder->feature($feature_2)->create();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->mainImg($main_image)
            ->gallery($imd_1, $imd_2)
            ->create()
        ;

        $this->inventoryFeatureValueBuilder->feature($feature_1)->inventory($model)->value($val_1)->create();
        $this->inventoryFeatureValueBuilder->feature($feature_1)->inventory($model)->value($val_2)->create();
        $this->inventoryFeatureValueBuilder->feature($feature_2)->inventory($model)->value($val_4)->create();

        $this->seoBuilder->model($model)->image($seo_img)->create();

        /** @var $command InventoryUpdateCommand */
        $command = resolve(InventoryUpdateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['guid'], $model->id);
        $this->assertEquals($res['slug'], $model->slug);
        $this->assertEquals($res['sort'], 0);
        $this->assertEquals($res['active'], $model->active);
        $this->assertEquals($res['min_limit'], $model->min_limit);
        $this->assertEquals($res['discount'], $model->discount);
        $this->assertEquals($res['quantity'], $model->quantity);
        $this->assertEquals($res['novelty'], $model->is_new);
        $this->assertEquals($res['popular'], $model->is_popular);
        $this->assertEquals($res['sale'], $model->is_sale);
        $this->assertEquals($res['delivery_cost'], $model->delivery_cost);
        $this->assertEquals($res['cost'], $model->price_retail);
        $this->assertEquals($res['old_cost'], $model->old_price);
        $this->assertEquals($res['length'], $model->length);
        $this->assertEquals($res['width'], $model->width);
        $this->assertEquals($res['height'], $model->height);
        $this->assertEquals($res['weight'], $model->weight);
        $this->assertEquals($res['package'], $model->package_type->value);
        $this->assertEquals($res['for_shop'], $model->for_shop);
        $this->assertEquals($res['sku'], $model->stock_number);
        $this->assertEquals($res['article_number'], $model->article_number);
        $this->assertEquals($res['category_guid'], $model->category_id);
        $this->assertEquals($res['brand_guid'], $model->brand_id);

        $this->assertCount(2, $res['features']);
        $this->assertCount(2, $res['features'][0]['values']);
        $this->assertCount(1, $res['features'][1]['values']);
        $this->assertEquals($res['features'][0]['id'], $feature_1->id);
        $this->assertEquals($res['features'][0]['values'][0]['id'], $val_1->id);
        $this->assertEquals($res['features'][0]['values'][1]['id'], $val_2->id);
        $this->assertEquals($res['features'][1]['id'], $feature_2->id);
        $this->assertEquals($res['features'][1]['values'][0]['id'], $val_4->id);

        $this->assertEquals($res['translations'][0]['language'], 'en');
        $this->assertEquals($res['translations'][0]['name'], $model->name);
        $this->assertEquals($res['translations'][0]['seo_h1'], $model->seo->h1);
        $this->assertEquals($res['translations'][0]['seo_title'], $model->seo->title);
        $this->assertEquals($res['translations'][0]['seo_description'], $model->seo->desc);
        $this->assertEquals($res['translations'][0]['seo_text'], $model->seo->text);
        $this->assertEquals($res['translations'][0]['description'], $model->notes);

//        $this->assertEquals($res['mainImage']['id'], $model->getMainImg()->id);
//        $this->assertEquals($res['mainImage']['name'], $model->getMainImg()->name);
//        $this->assertEquals($res['mainImage']['file_name'], $model->getMainImg()->file_name);
//        $this->assertEquals($res['mainImage']['mime_type'], $model->getMainImg()->mime_type);
//        $this->assertEquals($res['mainImage']['order_column'], $model->getMainImg()->order_column);
//        $this->assertEquals($res['mainImage']['size'], $model->getMainImg()->size);
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_ORIGINAL],  $this->fullUrl($model->getMainImg(),'original_webp'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_ORIGINAL . '_jpg'],  $this->fullUrl($model->getMainImg(),'original_jpg'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_SMALL],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_SMALL));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_SMALL . '_jpg'],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_SMALL. '_jpg'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_SMALL . '_2x'],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_SMALL. '_2x_webp'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_LARGE],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_LARGE));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_LARGE . '_jpg'],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_LARGE. '_jpg'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_LARGE . '_2x'],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_LARGE. '_2x_webp'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_MEDIUM],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_MEDIUM));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_MEDIUM . '_jpg'],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_MEDIUM. '_jpg'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_MEDIUM . '_2x'],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_MEDIUM. '_2x_webp'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_EXTRA_SMALL],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_EXTRA_SMALL));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_EXTRA_SMALL . '_jpg'],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_EXTRA_SMALL. '_jpg'));
//        $this->assertEquals($res['mainImage'][ImageAbstract::SIZE_EXTRA_SMALL . '_2x'],  $this->fullUrl($model->getMainImg(), ImageAbstract::SIZE_EXTRA_SMALL. '_2x_webp'));

//        $this->assertCount(2, $res['gallery']);
//        $this->assertEquals($res['gallery'][0]['id'], $model->getGallery()[0]->id);
//        $this->assertEquals($res['gallery'][1]['id'], $model->getGallery()[1]->id);
    }

    /** @test */
    public function check_prepare_data_float_quantity()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->quantity(2.7)
            ->create()
        ;

        /** @var $command InventoryUpdateCommand */
        $command = resolve(InventoryUpdateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['quantity'], 2);

    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->create()
        ;

        /** @var $command InventoryUpdateCommand */
        $command = resolve(InventoryUpdateCommand::class);

        $this->assertEquals(
            $command->getUri(['guid' => $model->id]),
            str_replace('{id}', $model->id, config("requests.e_com.paths.inventory.update"))
        );
    }

    /** @test */
    public function fail_uri()
    {
        /** @var $command InventoryUpdateCommand */
        $command = resolve(InventoryUpdateCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage(
            "For this command [InventoryUpdateCommand] you need to pass 'guid' to uri"
        );

        $command->getUri($data);
    }
}
