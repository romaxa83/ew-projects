<?php

namespace Tests\Feature\Http\Api\OneC\Companies;

use App\Models\Companies\Company;
use App\Services\Companies\CompanyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyPriceBuilder;
use Tests\TestCase;

class AddPriceTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected CompanyBuilder $companyBuilder;
    protected CompanyPriceBuilder $companyPriceBuilder;
    protected ProductBuilder $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->companyPriceBuilder = resolve(CompanyPriceBuilder::class);
    }

    /** @test */
    public function add_price(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $this->assertEmpty($model->prices);

        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'price' => random_int(100, 1000)
                ],
                [
                    'product_guid' => $product_2->guid,
                    'price' => random_int(100, 1000)
                ],
                [
                    'product_guid' => $product_3->guid,
                    'price' => random_int(100, 1000)
                ]
            ]
        ];

        $this->postJson(route('1c.companies.add-prices', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [],
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->prices[0]->product_id, $product_1->id);
        $this->assertEquals($model->prices[0]->price, data_get($data, 'data.0.price'));
        $this->assertNull($model->prices[0]->desc);
        $this->assertEquals($model->prices[1]->product_id, $product_2->id);
        $this->assertEquals($model->prices[1]->price, data_get($data, 'data.1.price'));
        $this->assertNull($model->prices[1]->desc);
        $this->assertEquals($model->prices[2]->product_id, $product_3->id);
        $this->assertEquals($model->prices[2]->price, data_get($data, 'data.2.price'));
        $this->assertNull($model->prices[2]->desc);
    }

    /** @test */
    public function add_price_with_desc(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $this->assertEmpty($model->prices);

        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'price' => random_int(100, 1000),
                    'description' => $this->faker->sentence
                ],
                [
                    'product_guid' => $product_2->guid,
                    'price' => random_int(100, 1000),
                    'description' => $this->faker->sentence
                ],
                [
                    'product_guid' => $product_3->guid,
                    'price' => random_int(100, 1000)
                ]
            ]
        ];

        $this->postJson(route('1c.companies.add-prices', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [],
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->prices[0]->product_id, $product_1->id);
        $this->assertEquals($model->prices[0]->price, data_get($data, 'data.0.price'));
        $this->assertEquals($model->prices[0]->desc, data_get($data, 'data.0.description'));
        $this->assertEquals($model->prices[1]->product_id, $product_2->id);
        $this->assertEquals($model->prices[1]->price, data_get($data, 'data.1.price'));
        $this->assertEquals($model->prices[1]->desc, data_get($data, 'data.1.description'));
        $this->assertEquals($model->prices[2]->product_id, $product_3->id);
        $this->assertEquals($model->prices[2]->price, data_get($data, 'data.2.price'));
        $this->assertNull($model->prices[2]->desc);
    }

    /** @test */
    public function add_new_price(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_2)->setCompany($model)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_3)->setCompany($model)->create();

        $this->assertCount(2, $model->prices);

        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'price' => random_int(100, 1000)
                ],
            ]
        ];

        $this->postJson(route('1c.companies.add-prices', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [],
                'success' => true
            ]);

        $model->refresh();

        $this->assertCount(3, $model->prices);
    }

    /** @test */
    public function update_some_price_and_desc(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_2)
            ->setCompany($model)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_3)
            ->setCompany($model)->create();

        $this->assertCount(2, $model->prices);

        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'price' => random_int(3000, 4000),
                    'description' => $this->faker->sentence,
                ],
                [
                    'product_guid' => $product_2->guid,
                    'price' => random_int(3000, 4000),
                    'description' => $this->faker->sentence,
                ],
            ]
        ];

        $this->assertEquals($product_2->id, $price_1->product_id);
        $this->assertNotEquals(data_get($data, 'data.1.price'), $price_1->price);
        $this->assertNotEquals(data_get($data, 'data.1.description'), $price_1->desc);

        $this->postJson(route('1c.companies.add-prices', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [],
                'success' => true
            ]);

        $model->refresh();
        $price_1->refresh();

        $this->assertCount(3, $model->prices);
        $this->assertEquals($product_2->id, $price_1->product_id);
        $this->assertEquals(data_get($data, 'data.1.price'), $price_1->price);
        $this->assertEquals(data_get($data, 'data.1.description'), $price_1->desc);

    }

    /** @test */
    public function ignore_product(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $guid_1 = $this->faker->uuid;
        $guid_2 = $this->faker->uuid;
        $product_3 = $this->productBuilder->create();

        $this->assertEmpty($model->prices);

        $data = [
            'data' => [
                [
                    'product_guid' => $guid_1,
                    'price' => random_int(100, 1000)
                ],
                [
                    'product_guid' => $guid_2,
                    'price' => random_int(100, 1000)
                ],
                [
                    'product_guid' => $product_3->guid,
                    'price' => random_int(100, 1000)
                ]
            ]
        ];

        $this->postJson(route('1c.companies.add-prices', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    $guid_1,
                    $guid_2
                ],
                'success' => true
            ])
            ->assertJsonCount(2, 'data')
        ;

        $model->refresh();

        $this->assertEquals($model->prices[0]->product_id, $product_3->id);
        $this->assertEquals($model->prices[0]->price, data_get($data, 'data.2.price'));
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $product_1 = $this->productBuilder->create();

        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'price' => random_int(100, 1000)
                ],
            ]
        ];

        $this->mock(CompanyService::class, function(MockInterface $mock){
            $mock->shouldReceive("addPrice")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('1c.companies.add-prices', ['guid' => $model->guid]), $data)
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);
    }
}
