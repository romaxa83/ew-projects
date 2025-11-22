<?php

namespace Tests\Feature\Mutations\Admin\Auth;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class RefreshToken extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function refresh_token_success()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->create();

        // сначала логинимся, чтоб получить refreshToken
        $response = $this->graphQL($this->queryStrLogin($builder))->assertOk();

        $responseData = $response->json('data.adminLogin');

        $refreshToken = $responseData['refreshToken'];

        $responseRefresh = $this->graphQL($this->queryStrRefresh($refreshToken));

        $responseRefreshData = $responseRefresh->json('data.adminRefreshToken');

        $this->assertArrayHasKey('refreshToken', $responseRefreshData);
        $this->assertArrayHasKey('expiresIn', $responseRefreshData);
        $this->assertArrayHasKey('tokenType', $responseRefreshData);
        $this->assertArrayHasKey('accessToken', $responseRefreshData);

        $this->assertNotEquals($responseRefreshData['refreshToken'], $refreshToken);
        $this->assertNotEquals($responseRefreshData['accessToken'], $responseData['accessToken']);
    }

    /** @test */
    public function refresh_token_wrong()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->create();

        // сначала логинимся, чтоб получить refreshToken
        $response = $this->graphQL($this->queryStrLogin($builder))->assertOk();

        $responseData = $response->json('data.adminLogin');

        $responseRefresh = $this->graphQL($this->queryStrRefresh('some_fake_refresh'));

        $this->assertArrayHasKey('errors', $responseRefresh);

        // @todo потенциально не верная проверка
        $this->assertEquals($responseRefresh['errors'][0]['debugMessage'], 'The refresh token is invalid.');
    }


    // @todo написать тесты на проверку ошибок при обновлении токена

    private function queryStrLogin($builder)
    {
        return sprintf('
            mutation {
                adminLogin(input:{email:"%s",password:"%s"}) {
                    refreshToken
                    expiresIn
                    tokenType
                    accessToken
              }
            }',
            $builder->getEmail(),
            $builder->getPassword()
        );
    }

    private function queryStrRefresh($refreshToken)
    {
        return sprintf('
            mutation {
                adminRefreshToken(input:{refreshToken:"%s"}) {
                    refreshToken
                    expiresIn
                    tokenType
                    accessToken
              }
            }',
            $refreshToken
        );
    }


}
