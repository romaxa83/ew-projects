<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Queries\FrontOffice\Orders\Dealer\ChangeProductPriceQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyPriceBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class ChangeProductPriceQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ChangeProductPriceQuery::NAME;

    protected DealerBuilder $dealerBuilder;
    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected CompanyBuilder $companyBuilder;
    protected CompanyPriceBuilder $companyPriceBuilder;
    protected ProductBuilder $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->companyPriceBuilder = resolve(CompanyPriceBuilder::class);
    }

    /** @test */
    public function change_price(): void
    {
        $company = $this->companyBuilder->create();

        $dealer = $this->dealerBuilder->setCompany($company)->create();
        $this->loginAsDealerWithRole($dealer);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)->setCompany($company)->create();
        $price_3 = $this->companyPriceBuilder->setProduct($product_3)->setCompany($company)->create();

        $order = $this->orderBuilder->setDealer($dealer)->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setData([
            'price' => $price_1->price
        ])->setOrder($order)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setData([
            'price' => $price_2->price
        ])->setOrder($order)->create();
        $item_3 = $this->itemBuilder->setProduct($product_3)
            ->setData([
                'price' => 5000
            ])->setOrder($order)->create();

        $this->assertEquals($price_1->price, $item_1->price);
        $this->assertEquals($price_2->price, $item_2->price);
        $this->assertNotEquals($price_3->price, $item_3->price);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;
    }

    /** @test */
    public function not_change_price(): void
    {
        $company = $this->companyBuilder->create();

        $dealer = $this->dealerBuilder->setCompany($company)->create();
        $this->loginAsDealerWithRole($dealer);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)->setCompany($company)->create();
        $price_3 = $this->companyPriceBuilder->setProduct($product_3)->setCompany($company)->create();

        $order = $this->orderBuilder->setDealer($dealer)->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setData([
            'price' => $price_1->price
        ])->setOrder($order)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setData([
            'price' => $price_2->price
        ])->setOrder($order)->create();
        $item_3 = $this->itemBuilder->setProduct($product_3)
            ->setData([
                'price' => $price_3->price
            ])->setOrder($order)->create();

        $this->assertEquals($price_1->price, $item_1->price);
        $this->assertEquals($price_2->price, $item_2->price);
        $this->assertEquals($price_3->price, $item_3->price);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => false
                ]
            ])
        ;
    }

    /** @test */
    public function empty_order_item(): void
    {
        $company = $this->companyBuilder->create();

        $dealer = $this->dealerBuilder->setCompany($company)->create();
        $this->loginAsDealerWithRole($dealer);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)->setCompany($company)->create();
        $price_3 = $this->companyPriceBuilder->setProduct($product_3)->setCompany($company)->create();

        $order = $this->orderBuilder->setDealer($dealer)->create();

        $this->assertEmpty($order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => false
                ]
            ])
        ;
    }

    /** @test */
    public function order_not_draft(): void
    {
        $company = $this->companyBuilder->create();

        $dealer = $this->dealerBuilder->setCompany($company)->create();
        $this->loginAsDealerWithRole($dealer);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)->setCompany($company)->create();
        $price_3 = $this->companyPriceBuilder->setProduct($product_3)->setCompany($company)->create();

        $order = $this->orderBuilder->setStatus(OrderStatus::APPROVED)
            ->setDealer($dealer)->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setData([
            'price' => $price_1->price
        ])->setOrder($order)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setData([
            'price' => $price_2->price
        ])->setOrder($order)->create();
        $item_3 = $this->itemBuilder->setProduct($product_3)
            ->setData([
                'price' => 5000
            ])->setOrder($order)->create();

        $this->assertEquals($price_1->price, $item_1->price);
        $this->assertEquals($price_2->price, $item_2->price);
        $this->assertNotEquals($price_3->price, $item_3->price);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => false
                ]
            ])
        ;
    }

    protected function getQueryStr($dealerId): string
    {
        return sprintf(
            '
            {
                %s (id: %s)
            }',
            self::MUTATION,
            $dealerId
        );
    }
}
