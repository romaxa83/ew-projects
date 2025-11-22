<?php

namespace Tests\Feature\Mutations\FrontOffice\Payments;

use App\Events\Payments\AddPaymentCardToMemberEvent;
use App\GraphQL\Mutations\FrontOffice\Payments\MemberAddCardMutation;
use App\Listeners\Payments\SendPaymentCardToOnecListeners;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Locations\State;
use App\Models\Payments\PaymentCard;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class MemberAddCardMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = MemberAddCardMutation::NAME;

    protected DealerBuilder $dealerBuilder;
    protected CompanyBuilder $companyBuilder;
    protected PaymentCardBuilder $paymentCardBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->paymentCardBuilder = resolve(PaymentCardBuilder::class);
    }

    /** @test */
    public function add_card_for_dealer(): void
    {
        Event::fake([AddPaymentCardToMemberEvent::class]);

        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();
        $this->loginAsDealerWithRole($dealer);

        $data = $this->data();
        $data['morph']['type'] = $dealer::MORPH_NAME;
        $data['morph']['id'] = $dealer->id;

        $this->assertEmpty($dealer->cards);

        $id = $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => data_get($data, 'payment_card.type'),
                        'code' => substr(data_get($data, 'payment_card.number'), -4),
                        'expiration_date' => data_get($data, 'payment_card.expiration_date'),
                        'default' => true,
                    ]
                ]
            ])
            ->json('data.'.self::MUTATION.'.id')
        ;

        /** @var $card PaymentCard */
        $card = PaymentCard::find($id);

        $dealer->refresh();

        $this->assertCount(1, $dealer->cards);

        $this->assertDatabaseHas(PaymentCard::class, [
            'member_type' => Dealer::MORPH_NAME,
            'member_id' => $dealer->id,
            'guid' => null
        ]);

        Event::assertDispatched(function (AddPaymentCardToMemberEvent $event) use ($card, $data) {

            return $event->getPaymentCard()->id === $card->id
                && $event->getDto()->type === data_get($data, 'payment_card.type')
                && $event->getDto()->name === data_get($data, 'payment_card.name')
                && $event->getDto()->number === data_get($data, 'payment_card.number')
                && $event->getDto()->expirationDate === data_get($data, 'payment_card.expiration_date')
                && $event->getDto()->cvc == data_get($data, 'payment_card.cvc')
                && $event->getDto()->billingAddress->stateID === data_get($data, 'billing_address.state_id')
                && $event->getDto()->billingAddress->city === data_get($data, 'billing_address.city')
                && $event->getDto()->billingAddress->addressLine1 === data_get($data, 'billing_address.address_line_1')
                && $event->getDto()->billingAddress->addressLine2 === data_get($data, 'billing_address.address_line_2')
                && $event->getDto()->billingAddress->zip === data_get($data, 'billing_address.zip')
                ;
        });
        Event::assertListening(AddPaymentCardToMemberEvent::class, SendPaymentCardToOnecListeners::class);
    }

    /** @test */
    public function add_new_card_for_dealer(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();
        $this->paymentCardBuilder->setMember($dealer)
            ->default()->create();

        $this->loginAsDealerWithRole($dealer);

        $data = $this->data();
        $data['morph']['type'] = $dealer::MORPH_NAME;
        $data['morph']['id'] = $dealer->id;

        $this->assertCount(1, $dealer->cards);

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => data_get($data, 'payment_card.type'),
                        'code' => substr(data_get($data, 'payment_card.number'), -4),
                        'expiration_date' => data_get($data, 'payment_card.expiration_date'),
                        'default' => false,
                    ]
                ]
            ])
        ;

        $dealer->refresh();

        $this->assertCount(2, $dealer->cards);

        $this->assertDatabaseHas(PaymentCard::class, [
            'member_type' => Dealer::MORPH_NAME,
            'member_id' => $dealer->id,
            'guid' => null
        ]);
    }

    /** @test */
    public function add_card_to_member_as_base_model(): void
    {
        Event::fake([AddPaymentCardToMemberEvent::class]);
        $this->loginAsDealerWithRole();

        $company = $this->companyBuilder->create();

        $data = $this->data();
        $data['morph']['type'] = Company::MORPH_NAME;
        $data['morph']['id'] = $company->id;

        $this->assertEmpty($company->cards);

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => data_get($data, 'payment_card.type'),
                        'code' => substr(data_get($data, 'payment_card.number'), -4),
                        'expiration_date' => data_get($data, 'payment_card.expiration_date'),
                        'default' => true,
                    ]
                ]
            ])
            ->json('data.'.self::MUTATION.'.id')
        ;

        $company->refresh();

        $this->assertCount(1, $company->cards);

        $this->assertDatabaseHas(PaymentCard::class, [
            'member_type' => Company::MORPH_NAME,
            'member_id' => $company->id,
            'guid' => null
        ]);

        Event::assertListening(AddPaymentCardToMemberEvent::class, SendPaymentCardToOnecListeners::class);
    }

    /** @test */
    public function fail_card_exist(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();

        $data = $this->data();
        $data['morph']['type'] = $dealer::MORPH_NAME;
        $data['morph']['id'] = $dealer->id;

        $d = $this->paymentCardBuilder->setMember($dealer)
            ->default()->setData([
                'hash' => md5(clear_str(data_get($data, 'payment_card.type'))
                    . clear_str(data_get($data, 'payment_card.number'))
                    . clear_str(data_get($data, 'payment_card.cvc'))
                    . clear_str(data_get($data, 'payment_card.expiration_date'))),
            ])->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertCount(1, $dealer->cards);

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.payment.card.exist')]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_wrong_morph_model(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();

        $data = $this->data();
        $data['morph']['type'] = 'wrong';
        $data['morph']['id'] = $dealer->id;

        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => 'Field "memberAddPaymentCard" argument "morph" requires type PaymentCardMorphTypeEnumType, found wrong.']
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $dealer Dealer */
        $this->dealerBuilder->create();

        $company = $this->companyBuilder->create();

        $data = $this->data();
        $data['morph']['type'] = Company::MORPH_NAME;
        $data['morph']['id'] = $company->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
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
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();
        $this->loginAsDealer($dealer);

        $company = $this->companyBuilder->create();

        $data = $this->data();
        $data['morph']['type'] = Company::MORPH_NAME;
        $data['morph']['id'] = $company->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
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
        $state_1 = State::with('country')->first();
        $cvc = random_int(100, 200);
        return [
            'payment_card' => [
                'type' => $this->faker->creditCardType,
                'name' => $this->faker->name,
                'number' => $this->faker->creditCardNumber,
                'cvc' => $cvc,
                'expiration_date' => $this->faker->creditCardExpirationDateString,
            ],
            'billing_address' => [
                'country_code' => $state_1->country->country_code,
                'state_id' => $state_1->id,
                'city' => $this->faker->city,
                'address_line_1' => $this->faker->streetName,
                'address_line_2' => $this->faker->streetName,
                'zip' => $this->faker->postcode,
            ]
        ];
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    morph: {
                        type: %s
                        id: "%s"
                    }
                    payment_card: {
                        type: "%s"
                        name: "%s"
                        number: "%s"
                        cvc: "%s"
                        expiration_date: "%s"
                    }
                    billing_address: {
                        country_code: "%s"
                        state_id: "%s"
                        city: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        zip: "%s"
                    }
                ) {
                    id
                    type
                    code
                    expiration_date
                    default
                }
            }',
            self::MUTATION,
            data_get($data, 'morph.type'),
            data_get($data, 'morph.id'),
            data_get($data, 'payment_card.type'),
            data_get($data, 'payment_card.name'),
            data_get($data, 'payment_card.number'),
            data_get($data, 'payment_card.cvc'),
            data_get($data, 'payment_card.expiration_date'),
            data_get($data, 'billing_address.country_code'),
            data_get($data, 'billing_address.state_id'),
            data_get($data, 'billing_address.city'),
            data_get($data, 'billing_address.address_line_1'),
            data_get($data, 'billing_address.address_line_2'),
            data_get($data, 'billing_address.zip'),
        );
    }
}
