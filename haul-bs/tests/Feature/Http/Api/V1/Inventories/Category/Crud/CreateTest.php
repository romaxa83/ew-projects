<?php

namespace Feature\Http\Api\V1\Inventories\Category\Crud;

use App\Events\Events\Inventories\Categories\CreateCategoryEvent;
use App\Events\Listeners\Inventories\Categories\SyncEComCreateCategoryListener;
use App\Models\Forms\Draft;
use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        $this->categoryBuilder = resolve(CategoryBuilder::class);

        parent::setUp();

        $cat = $this->categoryBuilder->create();

        $this->data = [
            'name' => 'category',
            'slug' => 'category',
            'desc' => 'category desc',
            'parent_id' => $cat->id,
            'position' => 2,
            'display_menu' => true
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([CreateCategoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $id = $this->postJson(route('api.v1.inventories.category.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'desc' => data_get($data, 'desc'),
                    'parent_id' => data_get($data, 'parent_id'),
                    'display_menu' => data_get($data, 'display_menu'),
                    'position' => data_get($data, 'position'),
                    'seo' => [
                        'h1' => null,
                        'title' => null,
                        'keywords' => null,
                        'desc' => null,
                        'text' => null,
                        'image' => null,
                    ],
                    'header_image' => null,
                    'menu_image' => null,
                    'mobile_image' => null,
                ],
            ])
            ->json('data.id')
        ;

        Event::assertDispatched(fn (CreateCategoryEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(CreateCategoryEvent::class, SyncEComCreateCategoryListener::class);
    }

    /** @test */
    public function success_create_with_header_images()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        unset($data['display_menu']);
        $data[Category::IMAGE_HEADER_FIELD_NAME] = UploadedFile::fake()->image('header.png');

        $this->postJson(route('api.v1.inventories.category.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    'header_image' => [
                        'id',
                        'name',
                        'file_name',
                        'mime_type',
                        'size',
                        'original',
                        'sm',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'menu_image' => null,
                    'mobile_image' => null,
                    'display_menu' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_create_with_menu_images()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[Category::IMAGE_MENU_FIELD_NAME] = UploadedFile::fake()->image('menu.png');

        $this->postJson(route('api.v1.inventories.category.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    'menu_image' => [
                        'id',
                        'name',
                        'file_name',
                        'mime_type',
                        'size',
                        'original',
                        'sm',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'header_image' => null,
                    'mobile_image' => null,
                ],
            ])
        ;
    }

    /** @test */
    public function success_create_with_mobile_images()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[Category::IMAGE_MOBILE_FIELD_NAME] = UploadedFile::fake()->image('mobile.png');

        $this->postJson(route('api.v1.inventories.category.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    'mobile_image' => [
                        'id',
                        'name',
                        'file_name',
                        'mime_type',
                        'size',
                        'original',
                        'original_jpg',
                        'xl',
                        'xl_jpg',
                        'xl_2x',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'header_image' => null,
                    'menu_image' => null,
                ],
            ])
        ;
    }

    /** @test */
    public function success_create_with_seo()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'category h1',
            'title' => 'category title',
            'keywords' => 'category keywords',
            'desc' => 'category desc',
            'text' => 'category text',
        ];

        $this->postJson(route('api.v1.inventories.category.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'seo' => [
                        'h1' => data_get($data, 'seo.h1'),
                        'title' => data_get($data, 'seo.title'),
                        'keywords' => data_get($data, 'seo.keywords'),
                        'desc' => data_get($data, 'seo.desc'),
                        'text' => data_get($data, 'seo.text'),
                        'image' => null,
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function success_create_with_seo_and_seo_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'category h1',
            'image' => UploadedFile::fake()->image('img.png'),
        ];

        $this->postJson(route('api.v1.inventories.category.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    'seo' => [
                        'image' => [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'size',
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
                    'name' => data_get($data, 'name'),
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
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.category.store'), $data, [
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
    public function field_success_role_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.category.store'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertFalse(Category::query()->where('slug', $data['slug'])->exists());
    }

    /** @test */
    public function create_draft_when_validate_form()
    {
        $user = $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $path = route('api.v1.inventories.category.store');

        $headers = [
            config('app.request_validation_only.header_key') => true,
            config('app.draft.header_key') => $path,
        ];

        $draftAttributes = [
            'user_id' => $user->id,
            'path' => $path,
        ];

        $this->assertDatabaseMissing(Draft::TABLE, $draftAttributes);

        $this->postJson(route('api.v1.inventories.category.store'), $data, $headers)
        ;

        $this->assertDatabaseHas(Draft::TABLE, $draftAttributes);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $this->categoryBuilder->slug('slug_1')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.category.store'), $data)
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
            ['parent_id', null, 'validation.required', ['attribute' => 'validation.attributes.parent_id']],
            ['parent_id', 99999, 'validation.exists', ['attribute' => 'validation.attributes.parent_id']],
//            ['display_menu', 12, 'validation.boolean', ['attribute' => 'validation.attributes.display_menu']],
//            ['display_menu', 'true', 'validation.boolean', ['attribute' => 'validation.attributes.display_menu']],
            ['position', null, 'validation.required', ['attribute' => 'validation.attributes.position']],
            ['position', 'a', 'validation.integer', ['attribute' => 'validation.attributes.position']],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.category.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.category.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
