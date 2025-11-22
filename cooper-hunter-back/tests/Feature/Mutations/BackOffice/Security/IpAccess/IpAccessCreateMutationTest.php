<?php

namespace Tests\Feature\Mutations\BackOffice\Security\IpAccess;

use App\GraphQL\Mutations\BackOffice\Security\IpAccessCreateMutation;
use App\Models\Admins\Admin;
use App\Models\Security\IpAccess;
use App\Permissions\Security\IpAccessCreatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class IpAccessCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = IpAccessCreateMutation::NAME;

    public function test_not_permitted_admin_has_unauthorized(): void
    {
        $this->loginAsAdmin();

        $this->test_guest_cant_add_new_ip();
    }

    public function test_guest_cant_add_new_ip(): void
    {
        $response = $this->query('192.168.0.1')
            ->assertOk();

        $this->assertGraphQlUnauthorized($response);
    }

    private function query(string $address, string $description = ''): TestResponse
    {
        $query = sprintf(
            'mutation { %s ( address: "%s", description: "%s", active: true ) { id address description active } }',
            self::MUTATION,
            $address,
            $description
        );

        return $this->postGraphQLBackOffice(compact('query'));
    }

    public function test_permitted_admin_can_create_new_ip_access(): void
    {
        $this->loginAsIpAccessManager();

        $address = '192.168.0.1';

        $this->assertDatabaseMissing(IpAccess::TABLE, [
            'address' => $address,
        ]);

        $response = $this->query($address)
            ->assertOk();

        $ipAccessArray = $response->json('data.'.self::MUTATION);

        self::assertNotNull($ipAccessArray['id']);

        $this->assertDatabaseHas(IpAccess::TABLE, [
            'address' => $address,
        ]);
    }

    public function loginAsIpAccessManager(): Admin
    {
        return $this->loginAsAdmin()->assignRole(
            $this->generateRole('Ip Access manager', [IpAccessCreatePermission::KEY], Admin::GUARD)
        );
    }

    public function test_has_validation_when_use_not_ip(): void
    {
        $this->loginAsIpAccessManager();

        $address = '192.168.500.1';
        $response = $this->query($address)
            ->assertOk();

        $this->assertResponseHasValidationMessage(
            $response,
            'address',
            [__('validation.ipv4', ['attribute' => 'address'])]
        );
    }
}
