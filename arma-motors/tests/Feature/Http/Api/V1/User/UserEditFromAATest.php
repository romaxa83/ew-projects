<?php

namespace Tests\Feature\Http\Api\V1\User;

use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class UserEditFromAATest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->armaAuth();
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function change_success()
    {
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->setUuid($data['uuid'])->create();
        $this->loginAsUser($user);
        $user->refresh();

        $this->assertNotEquals($user->status, $data['status']);
        $this->assertNotEquals($user->egrpoy, $data['codeOKPO']);
        $this->assertNotEquals($user->name, $data['name']);
        $this->assertNotEquals($user->email, $data['email']);
        $this->assertFalse($user->isVerify());

        $response = $this->post(
            route('api.v1.user.edit',['id' => $builder->getUuid()]),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertEquals($user->uuid, $data['uuid']);
        $this->assertEquals($user->status, $data['status']);
        $this->assertEquals($user->egrpoy, $data['codeOKPO']);
        $this->assertEquals($user->name, $data['name']);
        $this->assertEquals($user->email, $data['email']);
        $this->assertTrue($user->email_verify);
        $this->assertTrue($user->isVerify());
    }

    /** @test */
    public function change_only_verify()
    {
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->setUuid($data['uuid'])->create();
        $this->loginAsUser($user);
        $user->refresh();

        unset(
            $data['codeOKPO'],
            $data['status'],
            $data['name'],
            $data['email'],
        );

        $this->assertFalse($user->isVerify());

        $response = $this->post(
            route('api.v1.user.edit',['id' => $builder->getUuid()]),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertTrue($user->isVerify());
    }

    /** @test */
    public function change_email_exist_user()
    {
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->setUuid($data['uuid'])->create();
        $this->loginAsUser($user);
        $user->refresh();

        $data['email'] = $user->email->getValue();

        $this->assertEquals($user->email, $data['email']);

        $response = $this->post(
            route('api.v1.user.edit',['id' => $builder->getUuid()]),
            $data,
            $this->headers()
        )->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertEquals($user->email, $data['email']);
    }

    /** @test */
    public function change_email_exist_another_user()
    {
        $email = 'an_user@gmail.com';
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->setUuid($data['uuid'])->create();
        $userAnother = $builder->setEmail($email)->setPhone('+380954566677')->create();
        $userAnother->refresh();
        $this->loginAsUser($user);
        $user->refresh();

        $data['email'] = $email;

        $this->assertNotEquals($user->email, $data['email']);

        $response = $this->post(
            route('api.v1.user.edit',['id' => $builder->getUuid()]),
            $data,
            $this->headers()
        )->assertStatus(ErrorsCode::BAD_REQUEST);

        $this->assertFalse($response->json('success'));
        $this->assertEquals($response->json('data'), 'Email already exist another user');
    }

    /** @test */
    public function change_success_phone()
    {
        $data = $this->data();
        $builder = $this->userBuilder()->setUuid($data['uuid'])->setNewPhone('9876666666');
        $user = $builder->create();
        $this->loginAsUser($user);
        $user->refresh();

        $data['newPhone'] = $builder->getNewPhone()->getValue();

        $this->assertNotEquals($user->status, $data['status']);
        $this->assertNotEquals($user->phone->getValue(), $data['newPhone']);
        $this->assertNotNull($user->new_phone);
        $this->assertNull($user->phone_edit_at);

        $response = $this->post(
            route('api.v1.user.edit',['id' => $builder->getUuid()]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertEquals($user->uuid, $data['uuid']);
        $this->assertEquals($user->status, $data['status']);
        $this->assertEquals($user->phone->getValue(), $data['newPhone']);
        $this->assertNull($user->new_phone);
        $this->assertNotNull($user->phone_edit_at);
    }

    /** @test */
    public function send_phone_but_not_change()
    {
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->setUuid($data['uuid'])->create();
        $this->loginAsUser($user);
        $user->refresh();

        $data['newPhone'] = $builder->getPhone()->getValue();

        $this->assertEquals($user->phone->getValue(), $data['newPhone']);
        $this->assertNull($user->new_phone);
        $this->assertNull($user->phone_edit_at);

        $response = $this->post(
            route('api.v1.user.edit',['id' => $data['uuid']]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertEquals($user->phone->getValue(), $data['newPhone']);
        $this->assertNull($user->new_phone);
        $this->assertNull($user->phone_edit_at);
    }

    /** @test */
    public function new_phone_not_compare()
    {
        $data = $this->data();
        $builder = $this->userBuilder()->setUuid($data['uuid'])->setNewPhone('9876666666');
        $user = $builder->create();
        $this->loginAsUser($user);
        $user->refresh();

        $data['newPhone'] = $builder->getPhone()->getValue();

        $this->assertNotNull($user->new_phone);
        $this->assertNotEquals($user->new_phone->getValue(), $data['newPhone']);
        $this->assertNull($user->phone_edit_at);

        $response = $this->post(
            route('api.v1.user.edit',['id' => $builder->getUuid()]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertNotNull($user->new_phone);
        $this->assertNotEquals($user->new_phone->getValue(), $data['newPhone']);
        $this->assertNull($user->phone_edit_at);
    }

    /** @test */
    public function wrong_status()
    {
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->setUuid($data['uuid'])->create();
        $this->loginAsUser($user);
        $user->refresh();

        $data['status'] = 5;

        $response = $this->post(
            route('api.v1.user.edit',['id' => $builder->getUuid()]),
            $data,
            $this->headers()
        )->assertStatus(ErrorsCode::BAD_REQUEST);

        $this->assertEquals($response->json('data'), __('error.not valid user status', ['status' => $data['status']]));
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function not_found_user()
    {
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->create();
        $this->loginAsUser($user);
        $user->refresh();

        $response = $this->post(
            route('api.v1.user.edit',['id' => $data['uuid']]),
            $data,
            $this->headers()
        )->assertStatus(ErrorsCode::NOT_FOUND);

        $this->assertEquals($response->json('data'), __('error.not found model'));
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function wrong_auth_token()
    {
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->create();
        $this->loginAsUser($user);
        $user->refresh();

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->post(
            route('api.v1.user.edit',['id' => $data['uuid']]),
            $data,
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($response->json('data'), 'Bad authorization token');
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function without_auth_token()
    {
        $data = $this->data();
        $builder = $this->userBuilder();
        $user = $builder->create();
        $this->loginAsUser($user);
        $user->refresh();

        $response = $this->post(
            route('api.v1.user.edit',['id' => $data['uuid']]),
            $data,
            []
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($response->json('data'), 'Missing authorization header');
        $this->assertFalse($response->json('success'));
    }

    public function data(): array
    {
        return [
            'uuid' => '4e5d19f0-fc22-11eb-8274-4cd98fc26f15',
            'status' => 2,
            'codeOKPO' => '4e5d19f0',
            'name' => 'Иванов Иван Иванович',
            'verify' => true,
            'email' => "our_user@gamil.com",
        ];
    }
}


