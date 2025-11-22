<?php

namespace Tests\Feature\Mutations\User\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class EditPasswordTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $password = 'password';
        $user = $this->userBuilder()->setPassword($password)->create();
        $this->loginAsUser($user);

        $data = [
            'password' => 'new_password',
        ];

        $this->assertFalse(password_verify($data['password'], $user->password));

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.userEditPassword');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.user.password changed'));

        $user->refresh();
        $this->assertTrue(password_verify($data['password'], $user->password));
    }

    /** @test */
    public function wrong_short_password()
    {
        $password = 'password';
        $user = $this->userBuilder()->setPassword($password)->create();
        $this->loginAsUser($user);

        $data = [
            'password' => 'short',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals($response->json('errors.0.message'), "Validation failed for the field [userEditPassword].");
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                userEditPassword(input:{
                    password: "%s"
                }) {
                    message
                    status
                }
            }',
            $data['password'],
        );
    }
}



