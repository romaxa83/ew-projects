<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer;

use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\DeleteMutation;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class DeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = DeleteMutation::NAME;

    protected OrderBuilder $orderBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        $orderID = $order->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($orderID)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;

        $this->assertNull(Order::find($orderID));
    }

    /** @test */
    public function fail_dealer_not_owner(): void
    {
        $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $orderID = $order->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($orderID)
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
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setMain()->setData([
            'is_main_company' =>false
        ])->create();
        $this->loginAsDealerWithRole($dealer);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
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

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
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

        $this->loginAsDealer();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
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

