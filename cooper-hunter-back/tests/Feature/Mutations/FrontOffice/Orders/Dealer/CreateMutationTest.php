<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\DeliveryType;
use App\Enums\Orders\Dealer\OrderStatus;
use App\Enums\Orders\Dealer\OrderType;
use App\Enums\Orders\Dealer\PaymentType;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\CreateMutation;
use App\Models\Dealers\Dealer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class CreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CreateMutation::NAME;

    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_create_empty(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->dump()
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'type' => OrderType::ORDINARY(),
                        'delivery_type' => DeliveryType::NONE(),
                        'payment_type' => PaymentType::NONE(),
                        'po' => null,
                        'comment' => null,
                        'shipping_address' => null,
                        'dealer' => [
                            'id' => $dealer->id
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_create_for_main_dealer(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setMain()->setData([
            'is_main_company' =>false
        ])->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr()
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
        $this->postGraphQL([
            'query' => $this->getQueryStr()
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

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    public function data(): array
    {
        return [
            'order' => [
                'delivery_type' => DeliveryType::LTL(),
                'payment_type' => PaymentType::BANK(),
                'po' => '38099990904',
                'comment' => $this->faker->sentence,
            ]
        ];
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            mutation {
                %s {
                    id
                    status
                    type
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
            }',
            self::MUTATION,
        );
    }
}

