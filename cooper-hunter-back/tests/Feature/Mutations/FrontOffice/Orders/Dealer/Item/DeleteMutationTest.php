<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer\Item;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\Item\DeleteItemMutation;
use App\Models\Companies\Company;
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

class DeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = DeleteItemMutation::NAME;

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
    public function success_delete(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        $item = $this->itemBuilder->setOrder($order)->create();
        $this->itemBuilder->setOrder($order)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertCount(2, $order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;

        $order->refresh();
        $this->assertCount(1, $order->items);
    }

    /** @test */
    public function fail_order_is_not_draft(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)
            ->setStatus(OrderStatus::SHIPPED)->create();
        $item = $this->itemBuilder->setOrder($order)->create();
        $this->itemBuilder->setOrder($order)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertCount(2, $order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id)
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
        $item = $this->itemBuilder->setOrder($order)->create();
        $this->itemBuilder->setOrder($order)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertCount(2, $order->items);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.can't this order")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_delete_for_main_dealer(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setMain()->setData([
            'is_main_company' =>false
        ])->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        $item = $this->itemBuilder->setOrder($order)->create();
        $this->itemBuilder->setOrder($order)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id)
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
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $item = $this->itemBuilder->setOrder($order)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id)
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
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $item = $this->itemBuilder->setOrder($order)->create();

        $this->loginAsDealer();

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                )
            }',
            self::MUTATION,
            $id
        );
    }
}
