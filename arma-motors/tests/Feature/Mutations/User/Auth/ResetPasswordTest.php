<?php

namespace Tests\Feature\Mutations\User\Auth;

use App\Models\User\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\SmsVerifyBuilder;
use Tests\Traits\UserBuilder;

class ResetPasswordTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use SmsVerifyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $builder = $this->userBuilder()->phoneVerify()->setStatus(User::ACTIVE);
        $user = $builder->create();

        $data = [
            'phone' => $builder->getPhone()->getValue(),
            'password' => 'new_password',
            'actionToken' => $smsVerify->action_token->getValue()
        ];

        $this->assertFalse(password_verify($data['password'], $user->password));

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.userResetPassword');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.user.password changed'));

        $user->refresh();

        $this->assertTrue(password_verify($data['password'], $user->password));
    }

    /** @test */
    public function fail_not_valid_action_token()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $builder = $this->userBuilder()->phoneVerify()->setStatus(User::ACTIVE);
        $user = $builder->create();

        $data = [
            'phone' => $builder->getPhone()->getValue(),
            'password' => 'new_password',
            'actionToken' => 'not_valid'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found record by action token', ['action_token' => $data['actionToken']]), $response->json('errors.0.message'));
    }

    /** @test */
    public function fail_expired_action_token()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $builder = $this->userBuilder()->phoneVerify()->setStatus(User::ACTIVE);
        $user = $builder->create();

        $data = [
            'phone' => $builder->getPhone()->getValue(),
            'password' => 'new_password',
            'actionToken' => $smsVerify->action_token->getValue()
        ];

        CarbonImmutable::setTestNow(Carbon::now()->addDay());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.expired action token'), $response->json('errors.0.message'));
    }

    /** @test */
    public function fail_without_phone()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $builder = $this->userBuilder()->phoneVerify()->setStatus(User::ACTIVE);
        $user = $builder->create();

        $data = [
            'password' => 'new_password',
            'actionToken' => $smsVerify->action_token->getValue()
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutPhone($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function fail_without_password()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $builder = $this->userBuilder()->phoneVerify()->setStatus(User::ACTIVE);
        $user = $builder->create();

        $data = [
            'phone' => $builder->getPhone()->getValue(),
            'actionToken' => $smsVerify->action_token->getValue()
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutPassword($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function fail_without_action_token()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $builder = $this->userBuilder()->phoneVerify()->setStatus(User::ACTIVE);
        $user = $builder->create();

        $data = [
            'phone' => $builder->getPhone()->getValue(),
            'password' => 'new_password',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutActionToken($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    private function getQueryStr(array $data)
    {
        return sprintf('
            mutation {
                userResetPassword(input:{
                    phone: "%s",
                    password: "%s",
                    actionToken: "%s"
                }) {
                    message
                    status
              }
            }',
            $data['phone'],
            $data['password'],
            $data['actionToken']
        );
    }

    private function getQueryStrWithoutPhone(array $data)
    {
        return sprintf('
            mutation {
                userResetPassword(input:{
                    password: "%s",
                    actionToken: "%s"
                }) {
                    message
                    status
              }
            }',
            $data['password'],
            $data['actionToken']
        );
    }

    private function getQueryStrWithoutPassword(array $data)
    {
        return sprintf('
            mutation {
                userResetPassword(input:{
                    phone: "%s",
                    actionToken: "%s"
                }) {
                    message
                    status
              }
            }',
            $data['phone'],
            $data['actionToken']
        );
    }

    private function getQueryStrWithoutActionToken(array $data)
    {
        return sprintf('
            mutation {
                userResetPassword(input:{
                    password: "%s",
                    phone: "%s"
                }) {
                    message
                    status
              }
            }',
            $data['password'],
            $data['phone']
        );
    }
}

