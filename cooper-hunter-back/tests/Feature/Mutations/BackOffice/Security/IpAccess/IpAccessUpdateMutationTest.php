<?php

namespace Tests\Feature\Mutations\BackOffice\Security\IpAccess;

use App\GraphQL\Mutations\BackOffice\Security\IpAccessUpdateMutation;
use App\Models\Admins\Admin;
use App\Models\Security\IpAccess;
use App\Permissions\Security\IpAccessUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class IpAccessUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = IpAccessUpdateMutation::NAME;

    public function test_not_permitted_admin_has_unauthorized(): void
    {
        $this->loginAsAdmin();

        $this->test_guest_cant_update_new_ip();
    }

    public function test_guest_cant_update_new_ip(): void
    {
        $ipAccess = IpAccess::factory()
            ->create();
        $response = $this->query(['id' => $ipAccess->id, 'address' => '192.168.0.1',])
            ->assertOk();

        $this->assertGraphQlUnauthorized($response);
    }

    private function query(array $attributes): TestResponse
    {
        $query = sprintf(
            'mutation { %s (id: "%s", address: "%s", description: "%s", active: true ) { id address description active } }',
            self::MUTATION,
            $attributes['id'],
            $attributes['address'],
            $attributes['description'] ?? ''
        );

        return $this->postGraphQLBackOffice(compact('query'));
    }

    public function test_permitted_admin_can_create_new_ip_access(): void
    {
        $this->loginAsIpAccessManager();

        $ipAccess = IpAccess::factory()
            ->create();

        $newAddress = '8.8.8.8';
        $address = [
            'id' => $ipAccess->id,
            'address' => $newAddress,
        ];

        $this->assertDatabaseMissing(IpAccess::TABLE, $address);

        $this->query($address)
            ->assertOk();

        $this->assertDatabaseHas(IpAccess::TABLE, $address);
    }

    public function loginAsIpAccessManager(): Admin
    {
        return $this->loginAsAdmin()->assignRole(
            $this->generateRole('Ip Access manager', [IpAccessUpdatePermission::KEY], Admin::GUARD)
        );
    }
}
