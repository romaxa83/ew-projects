<?php

namespace Feature\Http\Api\V1\Inventories\Brand\Crud;

use App\Events\Events\Inventories\Brands\UpdateBrandEvent;
use App\Events\Listeners\Inventories\Brands\SyncEComUpdateBrandListener;
use App\Models\Inventories\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected BrandBuilder $brandBuilder;
    protected SeoBuilder $seoBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->brandBuilder = resolve(BrandBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);

        $this->data = [
            'name' => 'category',
            'slug' => 'category',
        ];
    }

    /** @test */
    public function success_update()
    {
        Event::fake([UpdateBrandEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $data = $this->data;

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'slug'));

        $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'seo' => null
                ],
            ])
        ;

        Event::assertDispatched(fn (UpdateBrandEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(UpdateBrandEvent::class, SyncEComUpdateBrandListener::class);
    }

    /** @test */
    public function success_update_nothing_change()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $data = $this->data;
        $data['name'] = $model->name;
        $data['slug'] = $model->slug;

        $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_seo()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $img = UploadedFile::fake()->image('img.png');

        $seo = $this->seoBuilder->model($model)->image($img)->create();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'seo h1',
            'title' => 'seo title',
            'keywords' => 'seo keywords',
            'desc' => 'seo desc',
            'text' => 'seo text',
        ];

        $this->assertNotEquals($model->seo->h1, data_get($data, 'seo.h1'));
        $this->assertNotEquals($model->seo->title, data_get($data, 'seo.title'));
        $this->assertNotEquals($model->seo->desc, data_get($data, 'seo.desc'));
        $this->assertNotEquals($model->seo->keywords, data_get($data, 'seo.keywords'));
        $this->assertNotEquals($model->seo->text, data_get($data, 'seo.text'));

        $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'seo' => [
                        'h1' => data_get($data, 'seo.h1'),
                        'title' => data_get($data, 'seo.title'),
                        'keywords' => data_get($data, 'seo.keywords'),
                        'desc' => data_get($data, 'seo.desc'),
                        'text' => data_get($data, 'seo.text'),
                    ]
                ],
            ])
        ;

        $seo->refresh();

        $this->assertEquals($seo->media[0]->name, 'img');
    }

    /** @test */
    public function success_update_seo_and_update_seo_image()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $img = UploadedFile::fake()->image('img.png');
        $img_1 = UploadedFile::fake()->image('img_1.png');

        $seo = $this->seoBuilder->model($model)->image($img)->create();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'category h1',
            'image' => $img_1,
        ];

        $this->assertNotEquals($seo->media[0]->name, 'img_1');

        $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    'seo' => [
                        'image' => [
                            'id',
                            'original',
                            'original_jpg',
                            'xl',
                            'xl_jpg',
                            'xl_2x',
                            'md',
                            'md_jpg',
                            'md_2x',
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'seo' => [
                        'h1' => data_get($data, 'seo.h1'),
                        'title' => null,
                        'keywords' => null,
                        'desc' => null,
                        'text' => null,
                    ]
                ],
            ])
        ;

        $seo->refresh();

        $this->assertEquals($seo->media[0]->name, 'img_1');
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $model->refresh();

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'name'));
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $this->brandBuilder->slug('slug')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['name', null, 'validation.required', ['attribute' => 'validation.attributes.name']],
            ['slug', null, 'validation.required', ['attribute' => 'validation.attributes.slug']],
            ['slug', 1111, 'validation.string', ['attribute' => 'validation.attributes.slug']],
            ['slug', 'slug', 'validation.unique', ['attribute' => 'validation.attributes.slug']],
        ];
    }

    /** @test */
    public function not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.brand.update', ['id' => 0]), $data);

        self::assertErrorMsg($res, __("exceptions.inventories.brand.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.brand.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
