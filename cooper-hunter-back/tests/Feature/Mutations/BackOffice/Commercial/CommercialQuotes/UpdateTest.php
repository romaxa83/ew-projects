<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\CommercialQuotes;

use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\GraphQL\Mutations\BackOffice\Commercial\CommercialQuotes\CommercialQuoteUpdateMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Commercial\QuoteBuilder;
use Tests\Builders\Commercial\QuoteItemBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialQuoteUpdateMutation::NAME;

    protected $quoteBuilder;
    protected $quoteItemBuilder;
    protected $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->quoteBuilder = resolve(QuoteBuilder::class);
        $this->quoteItemBuilder = resolve(QuoteItemBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
    }

    /** @test */
    public function success_update_all_field(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->create();
        $product = $this->productBuilder->create();

        $data = [
            'id' => $model->id,
            'status' => 'done',
            'email' => 'test_update@gmail.com',
            'send_detail_data' => 'false',
            'shipping_price' => 10.5,
            'tax' => 0.5,
            'discount_percent' => 7,
            'discount_sum' => 'null',
            'items' => [
                [
                    'name' => 'product_1',
                    'qty' => 3,
                    'price' => 5.5,
                ],
                [
                    'name' => 'product_2',
                    'qty' => 2,
                    'price' => 10,
                ],
                [
                    'product_id' => $product->id,
                    'qty' => 10,
                    'price' => 2,
                ],
            ]
        ];

        $model->refresh();

        $this->assertTrue($model->isPending());
        $this->assertTrue($model->send_detail_data);
        $this->assertEmpty($model->items);
        $this->assertNull($model->closed_at);
        $this->assertEquals(0, $model->shipping_price);
        $this->assertEquals(0, $model->tax);
        $this->assertNotEquals($model->email, $data['email']);
        $this->assertNull($model->discount_percent);
        $this->assertNull($model->discount_sum);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrAllData($data)
        ])
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $model->id,
                            'status' => data_get($data, 'status'),
                            'email' => data_get($data, 'email'),
                            'send_detail_data' => false,
                            'shipping_price' => data_get($data, 'shipping_price'),
                            'tax' => data_get($data, 'tax'),
                            'tax_sum' =>  pretty_price(((pretty_price(
                                (data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'))
                                + (data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'))
                                + (data_get($data, 'items.2.qty') * data_get($data, 'items.2.price'))
                                - pretty_price(
                                    ((data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'))
                                        + (data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'))
                                        + (data_get($data, 'items.2.qty') * data_get($data, 'items.2.price'))) / 100 * data_get($data, 'discount_percent')
                                )) * data_get($data, 'tax')) / 100)
                            ),
                            'discount_percent' => data_get($data, 'discount_percent'),
                            'discount_sum' => null,
                            'sub_total' => (data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'))
                                + (data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'))
                                + (data_get($data, 'items.2.qty') * data_get($data, 'items.2.price')),
                            'discount' => pretty_price(((data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'))
                                + (data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'))
                                + (data_get($data, 'items.2.qty') * data_get($data, 'items.2.price'))) / 100 * data_get($data, 'discount_percent')),
                            'total' => ((data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'))
                                + (data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'))
                                + (data_get($data, 'items.2.qty') * data_get($data, 'items.2.price')))
                                - pretty_price(((data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'))
                                        + (data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'))
                                        + (data_get($data, 'items.2.qty') * data_get($data, 'items.2.price'))) / 100 * data_get($data, 'discount_percent'))
                                +  pretty_price(((pretty_price(
                                            (data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'))
                                            + (data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'))
                                            + (data_get($data, 'items.2.qty') * data_get($data, 'items.2.price'))
                                            - pretty_price(
                                                ((data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'))
                                                    + (data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'))
                                                    + (data_get($data, 'items.2.qty') * data_get($data, 'items.2.price'))) / 100 * data_get($data, 'discount_percent')
                                            )) * data_get($data, 'tax')) / 100)
                                )
                                + data_get($data, 'shipping_price')
                            ,
                            'items' => [
                                [
                                    'name' => data_get($data, 'items.0.name'),
                                    'price' => data_get($data, 'items.0.price'),
                                    'qty' => data_get($data, 'items.0.qty'),
                                    'total' => data_get($data, 'items.0.qty') * data_get($data, 'items.0.price'),
                                ],
                                [
                                    'name' => data_get($data, 'items.1.name'),
                                    'price' => data_get($data, 'items.1.price'),
                                    'qty' => data_get($data, 'items.1.qty'),
                                    'total' => data_get($data, 'items.1.qty') * data_get($data, 'items.1.price'),
                                ],
                                [
                                    'name' => $product->title,
                                    'price' => data_get($data, 'items.2.price'),
                                    'qty' => data_get($data, 'items.2.qty'),
                                    'total' => data_get($data, 'items.2.qty') * data_get($data, 'items.2.price'),
                                ]
                            ]
                        ],
                    ]
                ]
            );

        $model->refresh();

        $this->assertTrue($model->isDone());
        $this->assertNotNull($model->closed_at);

    }

    /** @test */
    public function success_remove_items(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->create();

        $this->quoteItemBuilder->setQuoteId($model->id)->create();
        $this->quoteItemBuilder->setQuoteId($model->id)->create();

        $data = [
            'id' => $model->id,
            'status' => 'done',
        ];

        $model->refresh();

        $this->assertCount(2, $model->items);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $model->id,
                            'tax' => 0,
                            'tax_sum' => 0,
                            'discount_percent' => null,
                            'discount_sum' => null,
                            'discount' => 0,
                            'sub_total' => 0,
                            'total' => 0,
                        ],
                    ]
                ]
            );

        $model->refresh();

        $this->assertEmpty($model->items);

    }

    /** @test */
    public function toggle_status_from_pending_to_final(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();

        $data = [
            'id' => $model->id,
            'status' => 'final',
        ];

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'errors' => [
                        [
                            "message" => __('exceptions.commercial.quote.incorrect switching status')
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function toggle_status_from_done_to_pending(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();

        $data = [
            'id' => $model->id,
            'status' => 'pending',
        ];

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'errors' => [
                        [
                            "message" => __('exceptions.commercial.quote.incorrect switching status')
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function toggle_status_from_final_to_done(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::FINAL)->create();

        $data = [
            'id' => $model->id,
            'status' => 'done',
        ];

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'errors' => [
                        [
                            "message" => __('exceptions.commercial.quote.incorrect switching status')
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function toggle_status_from_final_to_pending(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::FINAL)->create();

        $data = [
            'id' => $model->id,
            'status' => 'pending',
        ];

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'errors' => [
                        [
                            "message" => __('exceptions.commercial.quote.incorrect switching status')
                        ]
                    ]
                ]
            );
    }

    protected function getQueryStrAllData(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                    input: {
                        status: %s
                        email: "%s"
                        send_detail_data: %s
                        shipping_price: %s
                        tax: %s
                        discount_percent: %s
                        discount_sum: %s
                        items: [
                            {
                                name: "%s",
                                qty: %s,
                                price: %s,
                            },
                            {
                                name: "%s",
                                qty: %s,
                                price: %s,
                            }
                            {
                                product_id: %s,
                                qty: %s,
                                price: %s,
                            }
                        ]
                    },
                ) {
                    id
                    email
                    status
                    send_detail_data
                    tax
                    tax_sum
                    discount_percent
                    discount_sum
                    discount
                    total
                    sub_total
                    shipping_price
                    items {
                        name
                        price
                        qty
                        total
                    }
                }
            }',
            self::MUTATION,
            $data['id'],
            $data['status'],
            $data['email'],
            $data['send_detail_data'],
            $data['shipping_price'],
            $data['tax'],
            $data['discount_percent'],
            $data['discount_sum'],
            $data['items'][0]['name'],
            $data['items'][0]['qty'],
            $data['items'][0]['price'],
            $data['items'][1]['name'],
            $data['items'][1]['qty'],
            $data['items'][1]['price'],
            $data['items'][2]['product_id'],
            $data['items'][2]['qty'],
            $data['items'][2]['price'],
        );
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                    input: {
                        status: %s
                        items: []
                    },
                ) {
                    id
                    email
                    status
                    send_detail_data
                    tax
                    tax_sum
                    discount_percent
                    discount_sum
                    discount
                    total
                    sub_total
                    shipping_price
                    items {
                        name
                        price
                        qty
                        total
                    }
                }
            }',
            self::MUTATION,
            $data['id'],
            $data['status'],
        );
    }
}
