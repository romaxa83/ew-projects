<?php

namespace Tests\Feature\Mutations\User\Auth;

use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\UserBuilder;

class LogoutTest extends TestCase
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
        $builder = $this->userBuilder()->phoneVerify()->setStatus(User::ACTIVE);
        $user = $builder->create();

        // логинимся
        $responseLogin = $this->graphQL($this->queryStrLogin($builder->getPhone(), $builder->getPassword()))->assertOk();
        ['userLogin' => $data] = $responseLogin->json('data');

        $responseForLookMe = $this->graphQL($this->queryStrMe())->withHeaders(['Authorization' => 'Bearer ' . $data['accessToken']]);

        $resData = $responseForLookMe->original['data']['authUser'];

        $this->assertEquals($user->id, $resData['id']);

        $responseLogout = $this->graphQL($this->queryStr())->withHeaders(['Authorization' => 'Bearer ' . $data['accessToken']]);

        $dataResponseLogout = $responseLogout->original['data']['userLogout'];

        $this->assertArrayHasKey('message', $dataResponseLogout);
        $this->assertArrayHasKey('status', $dataResponseLogout);
        $this->assertEquals(__('auth.user logout'), $dataResponseLogout['message']);
        $this->assertTrue($dataResponseLogout['status']);
    }

    private function queryStr()
    {
        return sprintf('
            mutation {
                userLogout {
                    message
                    status
              }
            }'
        );
    }

    public function queryStrMe(): string
    {
        return  sprintf('{
                authUser {id}
            }'
        );
    }

    public function queryStrLogin(string $phone, string $password): string
    {
        return sprintf('
            mutation {
                userLogin(input:{phone:"%s",password:"%s"}) {
                     accessToken
              }
            }',
            $phone,
            $password
        );
    }
}
