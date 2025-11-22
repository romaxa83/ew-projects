<?php

namespace Tests\Feature\Mutations\User\Auth;

use App\Events\Firebase\FcmPush;
use App\Events\User\EmailConfirm;
use App\Events\User\UserConfirmEmail;
use App\Exceptions\ErrorsCode;
use App\Listeners\Firebase\FcmPushListeners;
use App\Listeners\User\EmailConfirmListeners;
use App\Listeners\User\SendDataToUpdateUserListeners;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\EmailVerifyBuilder;
use Tests\Traits\UserBuilder;

class ConfirmEmailTest extends TestCase
{
    use DatabaseTransactions;
    use EmailVerifyBuilder;
    use UserBuilder;

    /** @test */
    public function success_confirm()
    {
        \Event::fake([
            UserConfirmEmail::class
        ]);

        $user = $this->userBuilder()->create();
        $emailBuilder = $this->emailVerifyBuilder()->setUser($user);
        $emailBuilder->create();
        $token = $emailBuilder->getEmailToken()->getValue();
//
        $this->assertFalse($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);
        $this->assertFalse($user->emailVerifyObj->verify);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($token)])
            ->assertOk();

        $responseData = $response->json('data.userConfirmEmail');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.email confirm'));

        $user->refresh();
        $this->assertTrue($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);
        $this->assertTrue($user->emailVerifyObj->verify);

        \Event::assertNotDispatched(UserConfirmEmail::class);
    }

    /** @test */
    public function confirm_if_email_verify()
    {
        $user = $this->userBuilder()->emailVerify()->create();
        $emailBuilder = $this->emailVerifyBuilder()->verify()->setUser($user);
        $emailBuilder->create();
        $token = $emailBuilder->getEmailToken()->getValue();
//
        $this->assertTrue($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);
        $this->assertTrue($user->emailVerifyObj->verify);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($token)])
            ->assertOk();

        $responseData = $response->json('data.userConfirmEmail');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.email verify'));

        $user->refresh();
        $this->assertTrue($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);
        $this->assertTrue($user->emailVerifyObj->verify);
    }

    /** @test */
    public function success_confirm_with_send_data_to_AA()
    {
        \Event::fake([
            UserConfirmEmail::class
        ]);

        $user = $this->userBuilder()->setUuid('74814d51-fc23-11eb-8274-4cd98fc26f15')->create();
        $emailBuilder = $this->emailVerifyBuilder()->setUser($user);
        $emailBuilder->create();
        $token = $emailBuilder->getEmailToken()->getValue();
//
        $this->assertFalse($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($token)])
            ->assertOk();

        $responseData = $response->json('data.userConfirmEmail');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.email confirm'));

        $user->refresh();
        $this->assertTrue($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);
        $this->assertTrue($user->emailVerifyObj->verify);

        \Event::assertDispatched(UserConfirmEmail::class);
        \Event::assertListening(UserConfirmEmail::class, SendDataToUpdateUserListeners::class);
    }

    /** @test */
    public function fail_expired_token()
    {
        \Event::fake([
            EmailConfirm::class,
            FcmPush::class
        ]);

        $user = $this->userBuilder()->create();
        $emailBuilder = $this->emailVerifyBuilder()->setUser($user);
        $emailVerify = $emailBuilder->create();
        $token = $emailBuilder->getEmailToken()->getValue();

        $this->assertFalse($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);
        $oldId = $user->emailVerifyObj->id;

        CarbonImmutable::setTestNow(Carbon::now()->addHour());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($token)]);

        $responseData = $response->json('data.userConfirmEmail');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.send new email confirm'));

        $user->refresh();
        $this->assertFalse($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);
        $this->assertNotEquals($user->emailVerifyObj->id, $oldId);

        // проверяет запустились ли события
        \Event::assertDispatched(EmailConfirm::class);
        \Event::assertDispatched(FcmPush::class);
        // проверяет какие обработчики обработали события
        \Event::assertListening(EmailConfirm::class, EmailConfirmListeners::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);
    }

    /** @test */
    public function fail_not_record()
    {
        $user = $this->userBuilder()->create();
        $emailBuilder = $this->emailVerifyBuilder()->setUser($user);
        $emailVerify = $emailBuilder->create();
        $token = '00000000-0000-0000-0000-000000000003';

        $this->assertFalse($user->email_verify);
        $this->assertNotEmpty($user->emailVerifyObj);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($token)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.email_verify.not found record by token', ['token' => $token]), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::EMAIL_TOKEN_NOT_FOUND_RECORD, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(string $token): string
    {
        return sprintf('
            mutation {
                userConfirmEmail(input:{
                    token: "%s",
                }) {
                    message
                    status
                }
            }',
            $token
        );
    }
}



