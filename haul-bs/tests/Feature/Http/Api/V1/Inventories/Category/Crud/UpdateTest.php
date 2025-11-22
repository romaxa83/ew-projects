<?php

namespace Feature\Http\Api\V1\Inventories\Category\Crud;

use App\Events\Events\Inventories\Categories\UpdateCategoryEvent;
use App\Events\Listeners\Inventories\Categories\SyncEComUpdateCategoryListener;
use App\Jobs\Categories\CategorySyncJob;
use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;
    protected SeoBuilder $seoBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);

        $cat = $this->categoryBuilder->create();

        $this->data = [
            'name' => 'category',
            'slug' => 'category',
            'desc' => 'category desc',
            'parent_id' => $cat->id,
            'position' => 4,
            'display_menu' => true
        ];
    }

    /** @test */
    public function success_update()
    {
        Queue::fake();

        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $data = $this->data;

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'slug'));
        $this->assertNotEquals($model->desc, data_get($data, 'desc'));
        $this->assertNotEquals($model->parent_id, data_get($data, 'parent_id'));
        $this->assertNotEquals($model->display_menu, data_get($data, 'display_menu'));
        $this->assertNotEquals($model->position, data_get($data, 'position'));

        $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'desc' => data_get($data, 'desc'),
                    'parent_id' => data_get($data, 'parent_id'),
                    'display_menu' => data_get($data, 'display_menu'),
                    'position' => data_get($data, 'position'),
                    'seo' => null
                ],
            ])
        ;

//        Queue::assertPushed(CategorySyncJob::class, 1);
    }

    /** @test */
    public function success_update_root_without_parent_id()
    {
        Queue::fake();

        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $data = $this->data;
        $data['parent_id'] = null;

        $this->assertNull($model->parent_id);

        $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'parent_id' => null,
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_nothing_change()
    {
        Queue::fake();

        $this->loginUserAsSuperAdmin();

        $parent = $this->categoryBuilder->create();

        /** @var $model Category */
        $model = $this->categoryBuilder->parent($parent)->create();

        $data = $this->data;
        $data['name'] = $model->name;
        $data['slug'] = $model->slug;
        $data['desc'] = $model->desc;
        $data['parent_id'] = $model->parent_id;
        $data['display_menu'] = $model->display_menu;
        $data['position'] = $model->position;

        $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'desc' => data_get($data, 'desc'),
                    'parent_id' => data_get($data, 'parent_id'),
                    'display_menu' => data_get($data, 'display_menu'),
                    'position' => data_get($data, 'position'),
                    'seo' => null
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_img()
    {
        Queue::fake();
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $header_img = UploadedFile::fake()->image('header.png');
        $menu_img = UploadedFile::fake()->image('menu.png');
        $mobile_img = UploadedFile::fake()->image('mobile.png');

        /** @var $model Category */
        $model = $this->categoryBuilder->menuImg($menu_img)->create();

        $data = $this->data;
        $data[Category::IMAGE_HEADER_FIELD_NAME] = $header_img;
        $data[Category::IMAGE_MOBILE_FIELD_NAME] = $mobile_img;

        $this->assertNotNull($model->getMenuImg());
        $this->assertNull($model->getHeaderImg());
        $this->assertNull($model->getMobileImg());

        $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    Category::IMAGE_HEADER_FIELD_NAME => [
                        'id',
                        'original',
                    ],
                    Category::IMAGE_MENU_FIELD_NAME => [
                        'id',
                        'original',
                    ],
                    Category::IMAGE_MOBILE_FIELD_NAME => [
                        'id',
                        'original',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->getMenuImg());
        $this->assertNotNull($model->getHeaderImg());
        $this->assertNotNull($model->getMobileImg());
    }

    /** @test */
    public function success_update_img_mobile()
    {
        Queue::fake();
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $menu_img = UploadedFile::fake()->image('menu.png');
        $mobile_img = UploadedFile::fake()->image('mobile.png');

        /** @var $model Category */
        $model = $this->categoryBuilder->menuImg($menu_img)->create();

        $data = $this->data;
        $data[Category::IMAGE_MOBILE_FIELD_NAME] = $mobile_img;

        $this->assertNotNull($model->getMenuImg());
        $this->assertNull($model->getHeaderImg());
        $this->assertNull($model->getMobileImg());

        $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    Category::IMAGE_MENU_FIELD_NAME => [
                        'id',
                        'original',
                    ],
                    Category::IMAGE_MOBILE_FIELD_NAME => [
                        'id',
                        'original',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->getMenuImg());
        $this->assertNull($model->getHeaderImg());
        $this->assertNotNull($model->getMobileImg());
    }

    /** @test */
    public function success_update_seo()
    {
        Queue::fake();
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $img = UploadedFile::fake()->image('img.png');

        $seo = $this->seoBuilder->model($model)->image($img)->create();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'category h1',
            'title' => 'category title',
            'keywords' => 'category keywords',
            'desc' => 'category desc',
            'text' => 'category text',
        ];

        $this->assertNotEquals($model->seo->h1, data_get($data, 'seo.h1'));
        $this->assertNotEquals($model->seo->title, data_get($data, 'seo.title'));
        $this->assertNotEquals($model->seo->desc, data_get($data, 'seo.desc'));
        $this->assertNotEquals($model->seo->keywords, data_get($data, 'seo.keywords'));
        $this->assertNotEquals($model->seo->text, data_get($data, 'seo.text'));

        $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data)
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
        Queue::fake();
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $img = UploadedFile::fake()->image('img.png');
        $img_1 = UploadedFile::fake()->image('img_1.png');

        $seo = $this->seoBuilder->model($model)->image($img)->create();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'category h1',
            'image' => $img_1,
        ];

        $this->assertNotEquals($seo->media[0]->name, 'img_1');

        $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    'seo' => [
                        'image' => [
                            'id',
                            'original',
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

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data, [
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

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $model->refresh();

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'name'));
        $this->assertNotEquals($model->parent_id, data_get($data, 'parent_id'));
        $this->assertNotEquals($model->desc, data_get($data, 'desc'));
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $parent = $this->categoryBuilder->slug('slug_1')->create();

        /** @var $model Category */
        $model = $this->categoryBuilder->parent($parent)->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['name', null, 'validation.required', ['attribute' => 'validation.attributes.name']],
            ['slug', null, 'validation.required', ['attribute' => 'validation.attributes.slug']],
            ['slug', 1111, 'validation.string', ['attribute' => 'validation.attributes.slug']],
            ['slug', 'slug_1', 'validation.unique', ['attribute' => 'validation.attributes.slug']],
            ['parent_id', 999999, 'validation.exists', ['attribute' => 'validation.attributes.parent_id']],
            ['parent_id', null, 'validation.required', ['attribute' => 'validation.attributes.parent_id']],
//            ['display_menu', 12, 'validation.boolean', ['attribute' => 'validation.attributes.display_menu']],
//            ['display_menu', 'true', 'validation.boolean', ['attribute' => 'validation.attributes.display_menu']],
            ['position', null, 'validation.required', ['attribute' => 'validation.attributes.position']],
            ['position', 'a', 'validation.integer', ['attribute' => 'validation.attributes.position']],
        ];
    }

    /** @test */
    public function not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.category.update', ['id' => 0]), $data);

        self::assertErrorMsg($res, __("exceptions.inventories.category.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.category.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
