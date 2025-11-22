<?php

namespace Feature\Http\Api\V1\Inventories\Brand\Crud;

use App\Events\Events\Inventories\Brands\CreateBrandEvent;
use App\Events\Listeners\Inventories\Brands\SyncEComCreateBrandListener;
use App\Models\Inventories\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected BrandBuilder $brandBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        $this->brandBuilder = resolve(BrandBuilder::class);

        parent::setUp();

        $this->data = [
            'name' => 'Brand',
            'slug' => 'brand',
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([CreateBrandEvent::class]);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $id = $this->postJson(route('api.v1.inventories.brand.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'hasRelatedEntities' => false,
                    'seo' => [
                        'h1' => null,
                        'title' => null,
                        'keywords' => null,
                        'desc' => null,
                        'text' => null,
                        'image' => null,
                    ],
                ],
            ])
            ->json('data.id')
        ;

        Event::assertDispatched(fn (CreateBrandEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(CreateBrandEvent::class, SyncEComCreateBrandListener::class);
    }

    /** @test */
    public function success_create_with_seo()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'seo h1',
            'title' => 'seo title',
            'keywords' => 'seo keywords',
            'desc' => 'seo desc',
            'text' => 'seo text',
        ];

        $this->postJson(route('api.v1.inventories.brand.store'), $data)
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
            'h1' => 'seo h1',
            'image' => UploadedFile::fake()->image('img.png'),
        ];

        $this->postJson(route('api.v1.inventories.brand.store'), $data)
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

        $res = $this->postJson(route('api.v1.inventories.brand.store'), $data, [
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

        $this->postJson(route('api.v1.inventories.brand.store'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertFalse(Brand::query()->where('slug', $data['slug'])->exists());
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $this->brandBuilder->slug('slug')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.brand.store'), $data)
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
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.brand.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.brand.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
