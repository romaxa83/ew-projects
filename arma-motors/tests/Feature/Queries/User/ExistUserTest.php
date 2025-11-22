<?php

namespace Tests\Feature\Queries\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class ExistUserTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function exist()
    {
        $phone = '3809543333';
        $user = $this->userBuilder()->setPhone($phone)->create();

        $response = $this->graphQL($this->getQueryStr($phone))
            ->assertOk();

        $responseData = $response->json('data.existUser');

        $this->assertArrayHasKey('code', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.user.user exist'));
    }

    /** @test */
    public function exist_if_user_in_archive()
    {
        $phone = '3809543333';
        $user = $this->userBuilder()->setPhone($phone)->softDeleted()->create();

        $response = $this->graphQL($this->getQueryStr($phone));

        $responseData = $response->json('data.existUser');

        $this->assertArrayHasKey('code', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.user.user exist'));
    }

    /** @test */
    public function not_exist()
    {
        $phone = '3809543333';

        $response = $this->graphQL($this->getQueryStr($phone))
            ->assertOk();

        $responseData = $response->json('data.existUser');

        $this->assertArrayHasKey('code', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertFalse($responseData['status']);
        $this->assertEquals($responseData['message'], __('error.not found user'));
    }

    public function getQueryStr(string $phone): string
    {
        return  sprintf('{
            existUser (phone: "%s"){
                code
                message
                status
                }
            }',
            $phone
        );
    }
}

