<?php

namespace Tests\Feature\Mutations\BackOffice\Security\IpAccess;

use App\GraphQL\Mutations\BackOffice\Security\IpAccessDeleteMutation;
use App\Models\Admins\Admin;
use App\Models\Security\IpAccess;
use App\Permissions\Security\IpAccessDeletePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class IpAccessDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const MUTATION = IpAccessDeleteMutation::NAME;

    public function test_not_permitted_admin_has_unauthorized(): void
    {
        $this->loginAsAdmin();

        $this->test_guest_cant_delete_ip();
    }

    public function test_guest_cant_delete_ip(): void
    {
        $ipAccess = IpAccess::factory()->create();

        $response = $this->query([$ipAccess->id])
            ->assertOk();

        $this->assertGraphQlUnauthorized($response);
    }

    private function query(array $deleting): TestResponse
    {
        $query = sprintf(
            'mutation { %s ( ids: [%s]) { message type } }',
            self::MUTATION,
            implode(', ', $deleting),
        );

        return $this->postGraphQLBackOffice(compact('query'));
    }

    public function test_permitted_admin_can_create_new_ip_access(): void
    {
        $this->loginAsIpAccessManager();

        $ipAccess = IpAccess::factory()->create();

        $this->assertDatabaseHas(IpAccess::TABLE, [
            'id' => $ipAccess->id,
        ]);

        $this->query([$ipAccess->id])
            ->assertOk();

        $this->assertDatabaseMissing(IpAccess::TABLE, [
            'id' => $ipAccess->id,
        ]);
    }

    public function loginAsIpAccessManager(): Admin
    {
        return $this->loginAsAdmin()->assignRole(
            $this->generateRole('Ip Access manager', [IpAccessDeletePermission::KEY], Admin::GUARD)
        );
    }

}
