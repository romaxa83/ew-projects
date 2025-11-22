<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer\Item;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\Item\UpdateItemMutation;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class UpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = UpdateItemMutation::NAME;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_update_qty(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setNotMain()->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        $qty = 10;
        /** @var $item Item */
        $item = $this->itemBuilder->setData([
            'price' => 1036
        ])->setOrder($order)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertNotEquals($item->qty, $qty);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id, $qty)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $item->id,
                        'qty' => $qty,
                        'amount' => pretty_price(($qty * $item->price) - $item->discount_total)
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_qty_as_zero(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setMain()->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        $qty = 0;
        /** @var $item Item */
        $item = $this->itemBuilder->setData([
            'price' => 1036
        ])->setOrder($order)->create();

        $this->loginAsDealerWithRole();

        $this->assertNotEquals($item->qty, $qty);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id, $qty)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "qty" => ["The qty must be at least 1."]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_update_for_main_dealer(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setMain()->setData([
            'is_main_company' =>false
        ])->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        $qty = 10;
        /** @var $item Item */
        $item = $this->itemBuilder->setData([
            'price' => 1036
        ])->setOrder($order)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id, $qty)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.not_action_for_main")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_order_is_not_draft(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setNotMain()->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer)->create();
        $qty = 10;
        /** @var $item Item */
        $item = $this->itemBuilder->setData([
            'price' => 1036
        ])->setOrder($order)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id, $qty)
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
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setNotMain()->create();
        $dealer_1 = $this->dealerBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer_1)->create();
        $qty = 10;
        /** @var $item Item */
        $item = $this->itemBuilder->setData([
            'price' => 1036
        ])->setOrder($order)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id, $qty)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.can't this order")]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        $qty = 10;
        /** @var $item Item */
        $item = $this->itemBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id, $qty)
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
        $qty = 10;
        /** @var $item Item */
        $item = $this->itemBuilder->create();

        $this->loginAsDealer();

        $this->postGraphQL([
            'query' => $this->getQueryStr($item->id, $qty)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    protected function getQueryStr($id, $qty): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                    qty: %s,
                ) {
                    id
                    qty
                    discount
                    amount
                }
            }',
            self::MUTATION,
            $id,
            $qty
        );
    }
}
