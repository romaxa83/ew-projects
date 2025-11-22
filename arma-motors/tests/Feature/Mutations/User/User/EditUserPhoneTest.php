<?php

namespace Tests\Feature\Mutations\User\User;

use App\Events\Firebase\FcmPush;
use App\Events\User\EditUser;
use App\Exceptions\ErrorsCode;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\SmsVerifyBuilder;
use Tests\Traits\UserBuilder;

class EditUserPhoneTest extends TestCase
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
    public function success_edit_by_active_user()
    {
        \Event::fake([
            FcmPush::class,
            EditUser::class
        ]);

        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $token = $smsVerify->action_token->getValue();

        $newPhone = '38999999999';
        $comment = 'some_comment';

        $user = $this->userBuilder()->setStatus(User::ACTIVE)->phoneVerify()->create();
        $this->loginAsUser($user);
        $user->refresh();

        $this->assertNotEquals($user->phone, $newPhone);
        $this->assertNull($user->new_phone);
        $this->assertNull($user->new_phone_comment);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($newPhone, $comment, $token)])
            ->assertOk();

        $responseData = $response->json('data.userEditPhone');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('phoneVerified', $responseData);
        $this->assertArrayHasKey('newPhone', $responseData);
        $this->assertArrayHasKey('newPhoneComment', $responseData);
        $this->assertArrayHasKey('newPhoneEditAt', $responseData);

        $this->assertNull($responseData['newPhone'], $newPhone);
        $this->assertNull($responseData['newPhoneEditAt'], $newPhone);
        $this->assertEquals($responseData['phone'], $newPhone);
        $this->assertEquals($responseData['newPhoneComment'], $comment);

        $user->refresh();
        $this->assertEquals($user->phone, $newPhone);

        \Event::assertDispatched(FcmPush::class);
        \Event::assertNotDispatched( EditUser::class);
    }

    /** @test */
    public function success_edit_by_verify_user()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $token = $smsVerify->action_token->getValue();

        $newPhone = '38999999999';
        $comment = 'some_comment';

        $user = $this->userBuilder()->setStatus(User::VERIFY)->phoneVerify()->create();
        $this->loginAsUser($user);
        $user->refresh();

        $this->assertNotEquals($user->phone, $newPhone);
        $this->assertNull($user->new_phone);
        $this->assertNull($user->new_phone_comment);
        $this->assertNull($user->phone_edit_at);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($newPhone, $comment, $token)])
            ->assertOk();

        $responseData = $response->json('data.userEditPhone');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('phoneVerified', $responseData);
        $this->assertArrayHasKey('newPhone', $responseData);
        $this->assertArrayHasKey('newPhoneComment', $responseData);
        $this->assertArrayHasKey('newPhoneEditAt', $responseData);

        $this->assertEquals($responseData['newPhone'], $newPhone);
        $this->assertEquals($responseData['newPhoneComment'], $comment);
        $this->assertNotNull($responseData['newPhoneEditAt']);

        $this->assertEquals($responseData['phone'], $user->phone);
    }

    /** @test */
    public function success_edit_by_verify_user_true_response_from_aa()
    {
        \Event::fake([
            FcmPush::class,
            EditUser::class
        ]);

        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $token = $smsVerify->action_token->getValue();

        $newPhone = '38999999999';
        $comment = 'some_comment';

        $user = $this->userBuilder()
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->setStatus(User::VERIFY)->phoneVerify()->create();
        $this->loginAsUser($user);
        $user->refresh();

        $this->assertNotEquals($user->phone, $newPhone);
        $this->assertNotNull($user->uuid);

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithResponse($newPhone, $comment, $token, "true")]);

        $responseData = $response->json('data.userEditPhone');

        $this->assertEquals($responseData['phone'], $user->phone);
        $this->assertEquals($responseData['newPhone'], $newPhone);
        $this->assertEquals($responseData['newPhoneComment'], $comment);
        $this->assertNotNull($responseData['newPhoneEditAt']);

        \Event::assertNotDispatched(FcmPush::class);
        \Event::assertDispatched(EditUser::class);
    }

    /** @test */
    public function success_edit_bu_user_not_have_uuid()
    {
        \Event::fake([
            FcmPush::class,
            EditUser::class
        ]);

        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $token = $smsVerify->action_token->getValue();

        $newPhone = '38999999999';
        $comment = 'some_comment';

        $user = $this->userBuilder()
            ->setStatus(User::VERIFY)->phoneVerify()->create();
        $this->loginAsUser($user);
        $user->refresh();

        $this->assertNotEquals($user->phone, $newPhone);
        $this->assertNull($user->uuid);

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithResponse($newPhone, $comment, $token, "true")]);

        $responseData = $response->json('data.userEditPhone');

        $this->assertEquals($responseData['phone'], $user->phone);
        $this->assertEquals($responseData['newPhone'], $newPhone);
        $this->assertEquals($responseData['newPhoneComment'], $comment);
        $this->assertNotNull($responseData['newPhoneEditAt']);

        \Event::assertNotDispatched(FcmPush::class);
        \Event::assertNotDispatched(EditUser::class);
    }

    /** @test */
    public function fail_new_phone_equals_old_phone()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $token = $smsVerify->action_token->getValue();

        $comment = 'some_comment';

        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);
        $newPhone = $user->phone;
        $user->refresh();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($newPhone, $comment, $token)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.new phone equals old', [
            'newPhone' => $newPhone,
            'oldPhone' => $user->phone
        ]), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::EDIT_PHONE_EQUALS_OLD, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function fail_without_action_token()
    {
        $newPhone = '38999999999';

        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);
        $user->refresh();

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutActionToken($newPhone)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function fail_wrong_action_token()
    {
        $token = 'wrongToken';
        $newPhone = '38999999999';
        $comment = 'some_comment';

        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);
        $user->refresh();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($newPhone, $comment, $token)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found record by action token', ['action_token' => $token]),
            $response->json('errors.0.message'));
    }

    /** @test */
    public function fail_exist_phone()
    {
        $phone1 = '389999999991';
        User::factory()->new(['phone' => new Phone($phone1)])->create();

        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($phone1)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.have user by this phone', ['phone' => $phone1]), $response->json('errors.0.message'));
    }

    /** @test */
    public function fail_not_auth()
    {
        $phone1 = '389999999991';
        User::factory()->new(['phone' => new Phone($phone1)])->create();

        $user = $this->userBuilder()->phoneVerify()->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($phone1)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(string $phone, $comment, $actionToken): string
    {
        return sprintf('
            mutation {
                userEditPhone(input:{
                    phone: "%s",
                    comment: "%s",
                    actionToken: "%s",
                }) {
                    id
                    phone
                    phoneVerified
                    newPhone
                    newPhoneComment
                    newPhoneEditAt
                }
            }',
            $phone,
            $comment,
            $actionToken,
        );
    }

    private function getQueryStrWithResponse(string $phone, $comment, $actionToken, $status): string
    {
        return sprintf('
            mutation {
                userEditPhone(input:{
                    phone: "%s",
                    comment: "%s",
                    actionToken: "%s",
                    aaResponse: %s,
                }) {
                    id
                    phone
                    phoneVerified
                    newPhone
                    newPhoneComment
                    newPhoneEditAt
                }
            }',
            $phone,
            $comment,
            $actionToken,
            $status,
        );
    }

    private function getQueryStrWithoutComment(string $phone): string
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();
        $token = $smsVerify->action_token->getValue();

        return sprintf('
            mutation {
                userEditPhone(input:{
                    phone: "%s",
                    actionToken: "%s",
                }) {
                    id
                    phone
                    phoneVerified
                    newPhone
                    newPhoneComment
                }
            }',
            $phone,
            $token
        );
    }

    private function getQueryStrWithoutActionToken(string $phone): string
    {
        return sprintf('
            mutation {
                userEditPhone(input:{
                    phone: "%s",
                }) {
                    id
                    phone
                    phoneVerified
                    newPhone
                    newPhoneComment
                }
            }',
            $phone
        );
    }
}

