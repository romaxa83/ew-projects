<?php

namespace Tests\Feature\Mutations\FrontOffice\Dealers;

use App\Exceptions\Auth\TokenEncryptException;
use App\GraphQL\Mutations\FrontOffice\Members\MemberEmailConfirmationMutation;
use App\Models\Dealers\Dealer;
use App\Services\Dealers\DealerVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealerEmailVerificationMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberEmailConfirmationMutation::NAME;

    protected DealerVerificationService $service;
    protected DealerBuilder $dealerBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passportInit();

        $this->service = app(DealerVerificationService::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @throws TokenEncryptException */
    public function test_it_verify_email_success(): void
    {
        /** @var Dealer $dealer */
        $dealer = $this->dealerBuilder->setData([
            'email' => 'test@dealer.com',
            'email_verified_at' => null,
        ])->create();

        $dealer->refresh();

        $this->assertNull($dealer->email_verified_at);
        $this->assertNull($dealer->email_verification_code);

        $token = $this->service->encryptEmailToken($dealer);
        $this->assertNotNull($dealer->email_verification_code);

        $query = sprintf(
            'mutation { %s ( token: "%s" )}',
            self::MUTATION,
            $token
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        $dealer->refresh();

        $this->assertNotNull($dealer->email_verified_at);
    }
}
