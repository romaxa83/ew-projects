<?php

namespace Tests\Feature\Mutations\User\User;

use App\Events\User\EmailConfirm;
use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class EditUserEmailTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_as_set_email()
    {
        $user = $this->userBuilder()->setEmail(null)->create();
        $user->refresh();
        $this->loginAsUser($user);
        $email = 'test@test.com';

        $this->assertNotEquals($user->email, $email);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($email)])
            ->assertOk();

        $responseData = $response->json('data.userEditEmail');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('emailVerified', $responseData);

        $this->assertEquals($responseData['email'], $email);
        $this->assertFalse($responseData['emailVerified'], $email);
    }

    /** @test */
    public function success_as_edit_email()
    {
        \Event::fake([EmailConfirm::class]);

        $user = $this->userBuilder()->emailVerify()->create();
        $this->loginAsUser($user);
        $newEmail = 'test-new@test.com';

        $this->assertNotEquals($user->email, $newEmail);
        $this->assertTrue($user->email_verify);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($newEmail)]);

        $user->refresh();

        $this->assertEquals($user->email, $newEmail);
        $this->assertFalse($user->email_verify);

        \Event::assertDispatched(EmailConfirm::class);

//        // проверка на повторный запрос
//        $response = $this->postGraphQL(['query' => $this->getQueryStr('some.nwe.email@gamail.com')]);
//
//        $this->assertArrayHasKey('errors', $response->json());
//        $this->assertEquals(__('error.email_verify.active email token'), $response->json('errors.0.message'));
//        $this->assertEquals(ErrorsCode::ACTIVE_EMAIL_VERIFY_TOKEN, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function remove_email_send_empty()
    {
        $oldEmail = 'old.email@test.com';
        $newEmail = '';
        $user = $this->userBuilder()
            ->setEmail($oldEmail)->emailVerify()->create();
        $this->loginAsUser($user);

        $this->assertEquals($user->email, $oldEmail);
        $this->assertTrue($user->email_verify);

        $this->postGraphQL(['query' => $this->getQueryStr($newEmail)]);

        $user->refresh();

        $this->assertNull($user->email);
        $this->assertFalse($user->email_verify);
    }

    /** @test */
    public function remove_email_send_null()
    {
        $oldEmail = 'old.email@test.com';
        $newEmail = null;
        $user = $this->userBuilder()
            ->setEmail($oldEmail)->emailVerify()->create();
        $this->loginAsUser($user);

        $this->assertEquals($user->email, $oldEmail);
        $this->assertTrue($user->email_verify);

        $this->postGraphQL(['query' => $this->getQueryStr($newEmail)]);

        $user->refresh();

        $this->assertNull($user->email);
        $this->assertFalse($user->email_verify);
    }

    /** @test */
    public function wrong_not_email()
    {
        $user = $this->userBuilder()->emailVerify()->create();
        $this->loginAsUser($user);
        $newEmail = 'not_email';

        $response = $this->postGraphQL(['query' => $this->getQueryStr($newEmail)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_email()
    {
        $user = $this->userBuilder()->emailVerify()->create();
        $this->loginAsUser($user);

        $response = $this->postGraphQL(['query' => $this->getQueryStrNotEmail()]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('validation.must exist', ['attribute' => 'email']), $response->json('errors.0.message'));
    }

    /** @test */
    public function wrong_exist_email()
    {
        $builder = $this->userBuilder();
        $someEmail = 'someEmail@test.com';
        $someUser = $builder->setEmail($someEmail);

        $user = $builder->create();
        $this->loginAsUser($user);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someEmail)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    private function getQueryStr(?string $email): string
    {
        return sprintf('
            mutation {
                userEditEmail(input:{
                    email: "%s",
                }) {
                    id
                    name
                    email
                    emailVerified
                }
            }',
            $email,
        );
    }

    private function getQueryStrNotEmail(): string
    {
        return sprintf('
            mutation {
                userEditEmail(input:{

                }) {
                    id
                    name
                    email
                    emailVerified
                }
            }',
        );
    }
}

