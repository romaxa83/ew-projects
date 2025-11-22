<?php

namespace Tests\Feature\Mutations\FrontOffice\Dealers;

use App\Exceptions\Auth\TokenEncryptException;
use App\GraphQL\Mutations\FrontOffice\Members\MemberResetPasswordMutation;
use App\Services\Dealers\DealerVerificationService;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class DealerResetPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;

    public const MUTATION = MemberResetPasswordMutation::NAME;

    protected DealerVerificationService $service;
    protected DealerBuilder $dealerBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DealerVerificationService::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);

        Notification::fake();
    }

    /**
     * @throws TokenEncryptException
     */
    public function test_reset_password_success(): void
    {
        $email = new Email('user@example.com');
        $password = 'password';
        $dealer = $this->dealerBuilder->setData([
            'email' => $email,
        ])->setPassword($password)->create();

        $passwordNew = 'Password123';

        $query = sprintf(
            'mutation { %s (token: "%s" password: "%s" password_confirmation: "%s") }',
            self::MUTATION,
            $this->service->encryptEmailToken($dealer),
            $passwordNew,
            $passwordNew,
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);
    }
}
