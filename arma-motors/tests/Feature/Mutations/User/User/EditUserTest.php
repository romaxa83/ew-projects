<?php

namespace Tests\Feature\Mutations\User\User;

use App\Events\User\EditUser;
use App\Listeners\User\SendDataToUpdateUserListeners;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class EditUserTest extends TestCase
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
            EditUser::class
        ]);

        $user = $this->userBuilder()
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->phoneVerify()
            ->create();
        $this->loginAsUser($user);

        $data = [
            'name' => 'new_name',
            'deviceId' => 'update_device_id',
            'fcmToken' => 'update_fcm_token',
            'egrpoy' => 'update_fcm_token',
            'lang' => 'uk',
        ];

        $this->assertNotEquals($user->name, $data['name']);
        $this->assertNotEquals($user->device_id, $data['deviceId']);
        $this->assertNotEquals($user->fcm_token, $data['fcmToken']);
        $this->assertNotEquals($user->egrpoy, $data['egrpoy']);
        $this->assertNotEquals($user->lang, $data['lang']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.userEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('deviceId', $responseData);
        $this->assertArrayHasKey('fcmToken', $responseData);
        $this->assertArrayHasKey('lang', $responseData);
        $this->assertArrayHasKey('egrpoy', $responseData);

        $this->assertEquals($user->id, $responseData['id']);
        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['deviceId'], $data['deviceId']);
        $this->assertEquals($responseData['fcmToken'], $data['fcmToken']);
        $this->assertEquals($responseData['lang'], $data['lang']);
        $this->assertEquals($responseData['egrpoy'], $data['egrpoy']);

        \Event::assertDispatched(EditUser::class);
        \Event::assertListening(EditUser::class, SendDataToUpdateUserListeners::class);
    }

    /** @test */
    public function change_egrpoy_but_user_not_have_uuid()
    {
        \Event::fake([
            EditUser::class
        ]);

        $user = $this->userBuilder()
            ->phoneVerify()
            ->create();
        $this->loginAsUser($user);

        $data = [
            'egrpoy' => 'update_fcm_token',
        ];

        $this->assertNotEquals($user->egrpoy, $data['egrpoy']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrSomeField($data)])
            ->assertOk();

        $responseData = $response->json('data.userEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('deviceId', $responseData);
        $this->assertArrayHasKey('fcmToken', $responseData);
        $this->assertArrayHasKey('lang', $responseData);
        $this->assertArrayHasKey('egrpoy', $responseData);

        $this->assertEquals($responseData['egrpoy'], $data['egrpoy']);

        \Event::assertNotDispatched(EditUser::class);
    }

    /** @test */
    public function send_null_field()
    {
        \Event::fake([
            EditUser::class
        ]);

        $builder = $this->userBuilder()
            ->setEgrpoy('11111111')->setFcmToken('some_fcm_token')
            ->phoneVerify();
        $user = $builder->create();
        $this->loginAsUser($user);

        $data = [
            'egrpoy' => null,
        ];

        $this->assertNotNull($user->egrpoy);

        $response = $this->postGraphQL(['query' => $this->getQueryStrSomeField($data)]);

        $responseData = $response->json('data.userEdit');

        $user->refresh();

        $this->assertEquals($responseData['fcmToken'], $user->fcm_token);
        $this->assertNull($responseData['egrpoy']);

        \Event::assertNotDispatched(EditUser::class);
    }

    /** @test */
    public function send_null_name()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();
        $this->loginAsUser($user);

        $data = [
            'name' => null,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrFieldName($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'),
            __('error.field can\'t be empty', ['field' => __('validation.attributes.name')]));
    }

    /** @test */
    public function send_null_lang()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();
        $this->loginAsUser($user);

        $data = [
            'lang' => null,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrFieldLang($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'),
            __('error.field can\'t be empty', ['field' => __('validation.attributes.lang')]));
    }


    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                userEdit(input:{
                    name: "%s",
                    deviceId: "%s",
                    fcmToken: "%s",
                    egrpoy: "%s",
                    lang: "%s",
                }) {
                    id
                    name
                    phone
                    status
                    emailVerified
                    phoneVerified
                    egrpoy
                    deviceId
                    fcmToken
                    lang
                    locale {
                        name
                        locale
                    }
                    createdAt
                }
            }',
            $data['name'],
            $data['deviceId'],
            $data['fcmToken'],
            $data['egrpoy'],
            $data['lang'],
        );
    }

    private function getQueryStrSomeField(array $data): string
    {
        return sprintf('
            mutation {
                userEdit(input:{
                    egrpoy: "%s",
                }) {
                    id
                    name
                    phone
                    status
                    emailVerified
                    phoneVerified
                    egrpoy
                    deviceId
                    fcmToken
                    lang
                    locale {
                        name
                        locale
                    }
                    createdAt
                }
            }',
            $data['egrpoy'],
        );
    }

    private function getQueryStrFieldName(array $data): string
    {
        return sprintf('
            mutation {
                userEdit(input:{
                    name: "%s",
                }) {
                    id
                    name
                }
            }',
            $data['name'],
        );
    }

    private function getQueryStrFieldLang(array $data): string
    {
        return sprintf('
            mutation {
                userEdit(input:{
                    lang: "%s",
                }) {
                    id
                    name
                }
            }',
            $data['lang'],
        );
    }
}

