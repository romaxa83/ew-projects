<?php

namespace Tests\Feature\Mutations\Permission;

use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class DeleteRoleTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function delete_success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_DELETE)
            ->create();
        $this->loginAsAdmin($admin);

        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($role->id)])
//            ->dump()
            ->assertOk();

        $responseData = $response->json('data.roleDelete');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertTrue($responseData['status']);
    }

    private function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                roleDelete(id: "%s")
                {
                    status
                    message
                }
            }',
            $id
        );
    }
}

