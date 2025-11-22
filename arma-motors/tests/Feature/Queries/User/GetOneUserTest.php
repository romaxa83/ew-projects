<?php

namespace Tests\Feature\Queries\User;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\User\User;
use App\Types\Permissions;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class GetOneUserTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function get_success()
    {
        User::factory()->count(5)->create();

        $builder = $this->adminBuilder();
        $mainAdmin = $builder->createRoleWithPerms([Permissions::USER_GET])->create();
        $this->loginAsAdmin($mainAdmin);

        $someUser = $this->userBuilder()->create();

        $response = $this->graphQL($this->getQueryStr($someUser->id))
            ->assertOk();

        $responseData = $response->json('data.user');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('phoneVerified', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('emailVerified', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('lang', $responseData);
        $this->assertArrayHasKey('egrpoy', $responseData);
        $this->assertArrayHasKey('locale', $responseData);
        $this->assertArrayHasKey('locale', $responseData['locale']);
        $this->assertArrayHasKey('name', $responseData['locale']);
        $this->assertArrayHasKey('createdAt', $responseData);
        $this->assertArrayHasKey('updatedAt', $responseData);

        $this->assertEquals($responseData['id'], $someUser->id);
        $this->assertEquals($responseData['email'], $someUser->email);
        $this->assertEquals($responseData['name'], $someUser->name);
        $this->assertEquals($responseData['status'], $this->user_status_draft);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_GET])
            ->create();

        $someUser = $this->userBuilder()->create();

        $response = $this->graphQL($this->getQueryStr($someUser->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ADMIN_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $someUser = $this->userBuilder()->create();

        $response = $this->graphQL($this->getQueryStr($someUser->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public function getQueryStr($id): string
    {
        return  sprintf('{
            user (id: %d){
                id
                phone
                phoneVerified
                email
                emailVerified
                status
                name
                lang
                locale {
                    name
                    locale
                }
                egrpoy
                createdAt
                updatedAt
               }
            }',
            $id
        );
    }
}

