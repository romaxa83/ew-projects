<?php

namespace Tests\Feature\Queries\FrontOffice\Permissions;

use App\GraphQL\Queries\FrontOffice\Permissions\TechnicianRolesQuery;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Models\Technicians\Technician;
use App\Permissions\Roles\RoleListPermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class TechnicianRolesQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = TechnicianRolesQuery::NAME;

    protected null|Collection $roles;

    public function test_can_get_list_of_technician_roles_for_permitted_user(): void
    {
        Language::factory()
            ->state(
                [
                    'slug' => 'ru'
                ]
            )
            ->create();

        $this->loginAsTechnician()->assignRole(
            $this->generateRole('Role1', [RoleListPermission::KEY], guard: Technician::GUARD)
        );

        $existingRoles = 2;
        $quantity = 2;
        $this->createTechnicianRoles($quantity);

        $query = sprintf(
            'query { %s (per_page: %s) {
                        data{
                            id
                            name
                            translation {id title language}
                            translations {id title language}
                            permissions {id name}
                            created_at
                            updated_at
                        }
                    } }',
            self::QUERY,
            50
        );

        $result = $this->postGraphQL(compact('query'));

        $roles = $result->json('data.'.self::QUERY.'.data');

        self::assertCount(
            $quantity + $existingRoles,
            $roles
        );
    }

    protected function createTechnicianRoles(int $quantity = 2): void
    {
        $this->roles = Role::factory()->times($quantity)->create(['guard_name' => Technician::GUARD]);
    }
}
