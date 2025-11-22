<?php

namespace Tests\Feature\Mutations\Admin\User;

use App\Exceptions\ErrorsCode;
use App\Models\User\User;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class UserChangeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use CarBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function change_success()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $user->refresh();

        $this->assertEquals(User::DRAFT, $user->status);

        $data = [
            'id' => $user->id,
            'status' => $this->user_status_verify
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $responseData = $response->json('data.adminChangeUserStatus');

        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['status'], $data['status']);

        $user->refresh();

        $this->assertEquals(User::VERIFY, $user->status);
    }

    /** @test */
    public function not_found_model()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $user->refresh();

        $data = [
            'id' => 999,
            'status' => $this->user_status_verify
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function wrong_status()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $user->refresh();

        $data = [
            'id' => $user->id,
            'status' => 'wrong'
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();

        $user = $this->userBuilder()->create();

        $data = [
            'id' => $user->id,
            'status' => $this->user_status_verify
        ];

        $response = $this->graphQL($this->getQuery($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function create_not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();

        $data = [
            'id' => $user->id,
            'status' => $this->user_status_verify
        ];

        $response = $this->graphQL($this->getQuery($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQuery(array $data): string
    {
        return sprintf('
            mutation {
                adminChangeUserStatus(input: {
                    id: "%s"
                    status: %s
                }) {
                    id
                    name
                    status
                }
            }',
            $data['id'],
            $data['status']
        );
    }
}
