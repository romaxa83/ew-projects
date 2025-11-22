<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\Enums\Orders\Dealer\PaymentType;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\CopyMutation;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class CopyMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CopyMutation::NAME;

    protected CompanyShippingAddressBuilder $companyShippingAddressBuilder;
    protected OrderBuilder $orderBuilder;
    protected DealerBuilder $dealerBuilder;
    protected PaymentCardBuilder $paymentCardBuilder;
    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyShippingAddressBuilder = resolve(CompanyShippingAddressBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->paymentCardBuilder = resolve(PaymentCardBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
    }

    /** @test */
    public function success_copy(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $card = $this->paymentCardBuilder->create();
        $address = $this->companyShippingAddressBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'payment_type' => PaymentType::CARD(),
            'payment_card_id' => $card->id,
        ])->setShippingAddress($address)->create();

        $item_1 = $this->itemBuilder->setOrder($order)->create();
        $item_2 = $this->itemBuilder->setOrder($order)->create();

        $id = $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.items')
            ->json('data.'.self::MUTATION.'.id')
        ;

        $copy = Order::find($id);

        $this->assertNotEquals($copy->id, $order->id);
        $this->assertNull($copy->po);
        $this->assertEquals($dealer->id, $copy->dealer_id);
        $this->assertEquals($order->payment_type, $copy->payment_type);
        $this->assertEquals($order->payment_card_id, $copy->payment_card_id);
        $this->assertEquals($order->delivery_type, $copy->delivery_type);
        $this->assertEquals($order->shipping_address_id, $copy->shipping_address_id);
        $this->assertEquals($order->comment, $copy->comment);
        $this->assertEquals(OrderStatus::DRAFT(), $copy->status);

        $this->assertNotEquals($copy->items[0]->id, $item_1->id);
        $this->assertEquals($copy->items[0]->product_id, $item_1->product_id);
        $this->assertEquals($copy->items[0]->qty, $item_1->qty);
        $this->assertEquals($copy->items[0]->price, $item_1->price);
        $this->assertEquals($copy->items[0]->discount, $item_1->discount);

        $this->assertNotEquals($copy->items[1]->id, $item_2->id);
        $this->assertEquals($copy->items[1]->product_id, $item_2->product_id);
        $this->assertEquals($copy->items[1]->qty, $item_2->qty);
        $this->assertEquals($copy->items[1]->price, $item_2->price);
        $this->assertEquals($copy->items[1]->discount, $item_2->discount);
    }

    /** @test */
    public function fail_update_for_main_dealer(): void
    {
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
        $this->loginAsDealer();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

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
                ) {
                    id
                    items {
                        id
                    }
                }
            }',
            self::MUTATION,
            $id
        );
    }
}
