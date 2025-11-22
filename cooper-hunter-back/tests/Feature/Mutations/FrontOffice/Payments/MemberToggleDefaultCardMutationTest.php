<?php

namespace Tests\Feature\Mutations\FrontOffice\Payments;

use App\GraphQL\Mutations\FrontOffice\Payments\MemberToggleDefaultCardMutation;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class MemberToggleDefaultCardMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberToggleDefaultCardMutation::NAME;

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
    public function success_toggle(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();

        $card_1 = $this->paymentCardBuilder->setMember($dealer)
            ->default()->create();
        $card_2 = $this->paymentCardBuilder->setMember($dealer)->create();
        $card_3 = $this->paymentCardBuilder->setMember($dealer)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertTrue($card_1->default);
        $this->assertFalse($card_2->default);
        $this->assertFalse($card_3->default);

        $this->postGraphQL([
            'query' => $this->getQueryStr($card_3->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $card_3->id,
                        'default' => true
                    ]
                ]
            ])
        ;

        $card_1->refresh();
        $card_2->refresh();
        $card_3->refresh();

        $this->assertFalse($card_1->default);
        $this->assertFalse($card_2->default);
        $this->assertTrue($card_3->default);
    }

    /** @test */
    public function success_toggle_company(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $card_1 = $this->paymentCardBuilder->setMember($company)
            ->default()->create();
        $card_2 = $this->paymentCardBuilder->setMember($company)->create();
        $card_3 = $this->paymentCardBuilder->setMember($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertTrue($card_1->default);
        $this->assertFalse($card_2->default);
        $this->assertFalse($card_3->default);

        $this->postGraphQL([
            'query' => $this->getQueryStr($card_3->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $card_3->id,
                        'default' => true
                    ]
                ]
            ])
        ;

        $card_1->refresh();
        $card_2->refresh();
        $card_3->refresh();

        $this->assertFalse($card_1->default);
        $this->assertFalse($card_2->default);
        $this->assertTrue($card_3->default);
    }

    /** @test */
    public function success_this_card(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();

        $card_1 = $this->paymentCardBuilder->setMember($dealer)
            ->default()->create();

        $this->loginAsDealerWithRole($dealer);

        $this->assertTrue($card_1->default);

        $this->postGraphQL([
            'query' => $this->getQueryStr($card_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $card_1->id,
                        'default' => true
                    ]
                ]
            ])
        ;

        $card_1->refresh();

        $this->assertTrue($card_1->default);
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
                ) {
                    id
                    default
                }
            }',
            self::MUTATION,
            $id
        );
    }
}

