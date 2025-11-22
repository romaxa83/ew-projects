<?php

namespace Tests\Feature\Mutations\FrontOffice\Payments;

use App\Events\Payments\AddPaymentCardToMemberEvent;
use App\Events\Payments\DeletePaymentCardFromMemberEvent;
use App\GraphQL\Mutations\FrontOffice\Payments\MemberDeleteCardMutation;
use App\Listeners\Payments\SendPaymentCardToOnecListeners;
use App\Models\Dealers\Dealer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class MemberDeleteCardMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberDeleteCardMutation::NAME;

    protected DealerBuilder $dealerBuilder;
    protected PaymentCardBuilder $paymentCardBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->paymentCardBuilder = resolve(PaymentCardBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        Event::fake([DeletePaymentCardFromMemberEvent::class]);

        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setMember($dealer)
            ->default()->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertCount(1, $dealer->cards);

        $this->postGraphQL([
            'query' => $this->getQueryStr($card->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;

        Event::assertDispatched(function (DeletePaymentCardFromMemberEvent $event) use ($card) {
            return $event->getPaymentCard()->id === $card->id;
        });
        Event::assertListening(DeletePaymentCardFromMemberEvent::class, SendPaymentCardToOnecListeners::class);

        $dealer->refresh();

        $this->assertEmpty($dealer->cards);
    }

    /** @test */
    public function fail_card_not_exist(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();

        $dealer_2 = $this->dealerBuilder->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr(1)
        ])
            ->assertJson([
                'errors' => [
                    ['extensions' => [
                        "validation" => [
                            "id" => ["The selected id is invalid."]
                        ]
                    ]]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setMember($dealer)
            ->default()->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($card->id)
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
        $card = $this->paymentCardBuilder->setMember($dealer)
            ->default()->create();
        $this->loginAsDealer($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr($card->id)
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
