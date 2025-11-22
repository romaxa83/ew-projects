<?php

namespace Tests\Feature\Queries\User;
use App\Events\Firebase\FcmPush;
use App\Events\User\EmailConfirm;
use App\Listeners\Firebase\FcmPushListeners;
use App\Listeners\User\EmailConfirmListeners;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class RequestVerifyEmailTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        \Event::fake([
            EmailConfirm::class,
            FcmPush::class,
        ]);

        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        $user->refresh();

        $this->assertNotNull($user->email);
        $this->assertFalse($user->email_verify);
        $this->assertNull($user->emailVerifyObj);

        $response = $this->postGraphQL(['query' => $this->getQueryStr()])
            ->assertOk();

        $user->refresh();
        $this->assertNotNull($user->emailVerifyObj);

        \Event::assertDispatched(EmailConfirm::class);
        \Event::assertDispatched(FcmPush::class);

        \Event::assertListening(EmailConfirm::class, EmailConfirmListeners::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);
    }

    /** @test */
    public function not_email()
    {
        $user = $this->userBuilder()->setEmail(null)->create();
        $this->loginAsUser($user);
        $user->refresh();

        $this->assertNull($user->email);
        $this->assertNull($user->emailVerifyObj);

        $response = $this->postGraphQL(['query' => $this->getQueryStr()])
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not email'), $response->json('errors.0.message'));
    }

    /** @test */
    public function email_verify_yet()
    {
        $user = $this->userBuilder()->emailVerify()->create();
        $this->loginAsUser($user);
        $user->refresh();

        $this->assertTrue($user->email_verify);

        $response = $this->postGraphQL(['query' => $this->getQueryStr()]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.email verify'), $response->json('errors.0.message'));
    }

    private function getQueryStr(): string
    {
        return sprintf('
             {
                userRequestVerifyEmail {
                    id
                    name
                    email
                    emailVerified
                }
            }',
        );
    }
}


