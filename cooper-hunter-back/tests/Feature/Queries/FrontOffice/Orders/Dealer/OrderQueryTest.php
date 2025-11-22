<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Queries\FrontOffice\Orders\Dealer\OrdersQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class OrderQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = OrdersQuery::NAME;

    protected DealerBuilder $dealerBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;
    protected OrderBuilder $orderBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }


    /** @test */
    public function get_order(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $order_1 = $this->orderBuilder->setDealer($dealer)->create();
        $order_2 = $this->orderBuilder->setDealer($dealer)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'id' => $order_1->id,
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStr($value): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                    data {
                        id
                        status
                        delivery_type
                        payment_type
                        po
                        comment
                        shipping_address {
                            id
                        }
                        dealer {
                            id
                        }
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function not_auth(): void
    {
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
        $dealer = $this->loginAsDealer();

        $order = $this->orderBuilder->setDealer($dealer)->create();

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
}

