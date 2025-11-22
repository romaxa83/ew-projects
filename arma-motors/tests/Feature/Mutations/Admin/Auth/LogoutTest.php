<?php

namespace Tests\Feature\Mutations\Admin\Auth;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class LogoutTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->create();

        // логинимся
        $responseLogin = $this->graphQL($this->queryStrLogin($builder->getEmail(), $builder->getPassword()))
            ->assertOk();

        ['adminLogin' => $data] = $responseLogin->json('data');


        $responseForLookMe = $this->graphQL($this->queryStrMe())->withHeaders(['Authorization' => 'Bearer ' . $data['accessToken']]);
        $resData = $responseForLookMe->original['data']['authAdmin'];

        $this->assertEquals($admin->id, $resData['id']);

        $responseLogout = $this->graphQL($this->queryStr())->withHeaders(['Authorization' => 'Bearer ' . $data['accessToken']]);
        $dataResponseLogout = $responseLogout->original['data']['adminLogout'];

        $this->assertArrayHasKey('message', $dataResponseLogout);
        $this->assertEquals(__('auth.admin logout'), $dataResponseLogout['message']);
    }

    private function queryStr()
    {
        return sprintf('
            mutation {
                adminLogout {
                    message
              }
            }'
        );
    }

    public function queryStrMe(): string
    {
        return  sprintf('{
                authAdmin {id}
            }'
        );
    }

    public function queryStrLogin(string $email, string $password): string
    {
        return sprintf('
            mutation {
                adminLogin(input:{email:"%s",password:"%s"}) {
                     accessToken
              }
            }',
            $email,
            $password
        );
    }
}
