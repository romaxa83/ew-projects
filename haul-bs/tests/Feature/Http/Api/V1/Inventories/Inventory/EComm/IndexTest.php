<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\EComm;

use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\InventoryFeatureValueBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected SeoBuilder $seoBuilder;
    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;
    protected InventoryFeatureValueBuilder $inventoryFeatureValueBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);
        $this->inventoryFeatureValueBuilder = resolve(InventoryFeatureValueBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $img_1 = UploadedFile::fake()->image('img_1.png');
        $img_2 = UploadedFile::fake()->image('img_2.png');

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->mainImg($img_1)->gallery($img_2)->create();

        $this->seoBuilder->model($m_1)->create();

        $feature_1 = $this->featureBuilder->create();
        $feature_2 = $this->featureBuilder->create();

        $value_1 = $this->featureValueBuilder->feature($feature_1)->create();
        $value_2 = $this->featureValueBuilder->feature($feature_1)->create();
        $value_3 = $this->featureValueBuilder->feature($feature_1)->create();

        $this->inventoryFeatureValueBuilder
            ->feature($feature_1)
            ->value($value_1)
            ->inventory($m_1)
            ->create();
        $this->inventoryFeatureValueBuilder
            ->feature($feature_1)
            ->value($value_2)
            ->inventory($m_1)
            ->create();

        $this->getJson(route('api.v1.e_comm.inventories'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'category_id',
                        'unit' => [
                            'id',
                            'name',
                            'accept_decimals',
                        ],
                        'supplier_id',
                        'brand_id',
                        'active',
                        'name',
                        'slug',
                        'stock_number',
                        'price_retail',
                        'min_limit_price',
                        'quantity',
                        'min_limit',
                        'notes',
                        'for_shop',
                        'length',
                        'width',
                        'height',
                        'weight',
                        'is_new',
                        'is_popular',
                        'is_sale',
                        'package_type',
                        'old_price',
                        'discount',
                        'delivery_cost',
                        'article_number',
                        'created_at',
                        'updated_at',
                        'main_image' => [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'size',
                            'original',
                            'sm',
                        ],
                        'gallery' => [
                            [
                                'id',
                                'name',
                                'file_name',
                                'mime_type',
                                'size',
                                'original',
                                'sm',
                            ]
                        ],
                        'seo' => [
                            'h1',
                            'title',
                            'keywords',
                            'desc',
                            'text',
                            'image',
                        ],
                        'features' => [
                            [
                                'id',
                                'name',
                                'values' => [
                                    [
                                        'id',
                                        'name',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.features')
            ->assertJsonCount(2, 'data.0.features.0.values')
        ;
    }

    /** @test */
    public function success_list_more()
    {
        $this->loginUserAsSuperAdmin();
        Inventory::factory()->count(100)->create();

        $this->getJson(route('api.v1.e_comm.inventories'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonCount(100, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.e_comm.inventories'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function wrong_token()
    {
        $res = $this->getJson(route('api.v1.e_comm.inventories'), [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
