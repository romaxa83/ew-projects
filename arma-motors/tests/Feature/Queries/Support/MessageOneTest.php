<?php

namespace Tests\Feature\Queries\Support;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\SupportBuilder;
use Tests\Traits\UserBuilder;

class MessageOneTest extends TestCase
{
    use DatabaseTransactions;
    use SupportBuilder;
    use AdminBuilder;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_GET])
            ->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();

        $builderSupport = $this->supportBuilder();
        $category = $builderSupport->onlyCategory()->create();

        $text = 'test';
        $email = 'test@test.com';
        $message = $builderSupport
            ->setCategoryId($category->id)
            ->setUserId($user->id)
            ->setText($text)
            ->setEmail($email)
            ->asOne()
            ->create();

        $response = $this->graphQL($this->getQueryStr($message->id))
            ->assertOk();

        $responseData = $response->json('data.message');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('text', $responseData);
        $this->assertArrayHasKey('createdAt', $responseData);
        $this->assertArrayHasKey('updatedAt', $responseData);
        $this->assertArrayHasKey('category', $responseData);
        $this->assertArrayHasKey('id', $responseData['category']);
        $this->assertArrayHasKey('current', $responseData['category']);
        $this->assertArrayHasKey('name', $responseData['category']['current']);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('id', $responseData['user']);
        $this->assertArrayHasKey('name', $responseData['user']);

        $this->assertEquals($responseData['category']['id'], $category->id);
        $this->assertEquals($responseData['category']['current']['name'], $category->current->name);
        $this->assertEquals($responseData['user']['id'], $user->id);
        $this->assertEquals($responseData['text'], $text);
        $this->assertEquals($responseData['email'], $email);
        $this->assertEquals($responseData['id'], $message->id);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_GET])
            ->create();

        $response = $this->graphQL($this->getQueryStr(1));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ADMIN_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(1));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public function getQueryStr($id): string
    {
        return  sprintf('{
            message(id: "%s") {
                id
                status
                category{
                    id
                    current {
                        name
                    }
                }
                user {
                    id
                    name
                }
                email
                text
                createdAt
                updatedAt
              }
            }',
            $id
        );
    }
}
