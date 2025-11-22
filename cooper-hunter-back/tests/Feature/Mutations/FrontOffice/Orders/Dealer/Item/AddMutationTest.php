<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer\Item;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\Item\AddItemMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Companies\Company;
use App\Models\Companies\Price;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyPriceBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class AddMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = AddItemMutation::NAME;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected DealerBuilder $dealerBuilder;
    protected CompanyBuilder $companyBuilder;
    protected CompanyPriceBuilder $companyPriceBuilder;
    protected ProductBuilder $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->companyPriceBuilder = resolve(CompanyPriceBuilder::class);
    }

    /** @test */
    public function success_add_item(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        /** @var $product Product */
        $product = $this->productBuilder->create();
        /** @var $price Price */
        $price = $this->companyPriceBuilder->setProduct($product)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertEmpty($order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $order->id,
                        'total_amount' => pretty_price(1 * $price->price),
                        'items' => [
                            [
                                'product' => [
                                    'id' => $product->id
                                ],
                                'price' => $price->price,
                                'qty' => 1,
                                'discount' => 0,
                                'amount' => pretty_price(1 * $price->price)
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.items')
        ;
    }

    /** @test */
    public function success_add_new(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        $item_1 = $this->itemBuilder->setOrder($order)->setData([
            'qty' => 5
        ])->create();
        $item_2 = $this->itemBuilder->setData([
            'qty' => 2
        ])->setOrder($order)->create();
        /** @var $product Product */
        $product = $this->productBuilder->create();
        /** @var $price Price */
        $price = $this->companyPriceBuilder->setProduct($product)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertCount(2, $order->items);

        $totalDiscount = pretty_price($item_1->discount + $item_2->discount);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $order->id,
                        'total_amount' => (pretty_price($item_1->qty * $item_1->price) + pretty_price($item_2->qty * $item_2->price) + pretty_price(1 * $price->price)) - $totalDiscount,
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::MUTATION.'.items')
        ;
    }

    /** @test */
    public function success_add_item_if_dealer_is_main_and_main_company(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder
            ->setData([
                'is_main' => true,
                'is_main_company' => true,
            ])->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        /** @var $product Product */
        $product = $this->productBuilder->create();
        /** @var $price Price */
        $price = $this->companyPriceBuilder->setProduct($product)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertEmpty($order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $order->id,
                        'total_amount' => pretty_price(1 * $price->price),
                        'items' => [
                            [
                                'product' => [
                                    'id' => $product->id
                                ],
                                'price' => $price->price,
                                'qty' => 1,
                                'discount' => 0,
                                'amount' => pretty_price(1 * $price->price)
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.items')
        ;
    }

    /** @test */
    public function fail_add_product_if_exist_it(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        /** @var $product Product */
        $product = $this->productBuilder->create();
        /** @var $price Price */
        $price = $this->companyPriceBuilder->setProduct($product)
            ->setCompany($company)->create();

        $item_1 = $this->itemBuilder->setOrder($order)->setData([
            'qty' => 5
        ])->setProduct($product)->create();
        $item_2 = $this->itemBuilder->setData([
            'qty' => 2
        ])->setOrder($order)->create();


        $this->loginAsDealerWithRole($dealer);

        $this->assertCount(2, $order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.item is already on order")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_order_is_not_draft(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setStatus(OrderStatus::BACKORDER)
            ->setDealer($dealer)->create();
        /** @var $product Product */
        $product = $this->productBuilder->create();
        /** @var $price Price */
        $price = $this->companyPriceBuilder->setProduct($product)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertEmpty($order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.order is not draft")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_dealer_not_owner(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer_1)->create();
        /** @var $product Product */
        $product = $this->productBuilder->create();
        /** @var $price Price */
        $price = $this->companyPriceBuilder->setProduct($product)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertEmpty($order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.can't this order")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_add_item_for_main_dealer(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->setData([
            'is_main_company' =>false
        ])->setMain()->create();
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $product Product */
        $product = $this->productBuilder->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.not_action_for_main")]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $product Product */
        $product = $this->productBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "Unauthorized"]
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $product Product */
        $product = $this->productBuilder->create();

        $this->loginAsDealer();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id, $product->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    protected function getQueryStr($id, $productID): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    order_id: %s,
                    product_id: %s,
                ) {
                    id
                    total_amount
                    items {
                        id
                        product {
                            id
                        }
                        price
                        qty
                        discount
                        amount
                    }
                }
            }',
            self::MUTATION,
            $id,
            $productID
        );
    }
}

