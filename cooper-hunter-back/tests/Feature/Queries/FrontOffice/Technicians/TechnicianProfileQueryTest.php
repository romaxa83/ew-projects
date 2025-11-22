<?php

namespace Tests\Feature\Queries\FrontOffice\Technicians;

use App\GraphQL\Queries\FrontOffice\Technicians\TechnicianProfileQuery;
use App\Models\Permissions\Permission;
use App\Models\Technicians\Technician;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class TechnicianProfileQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = TechnicianProfileQuery::NAME;

    public function test_get_technician_profile(): void
    {
        /** @var Collection $permissions */
        $permissions = Permission::factory()
            ->technician()
            ->count(5)
            ->create();
        $role = $this->generateRole(
            'test-role',
            $permissions->pluck('name')->all(),
            Technician::GUARD,
        );

        $technician = Technician::factory()->certified()->create();

        $technician->assignRole($role);

        $this->loginAsTechnician($technician);

        $this->query()
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $technician->id,
                            'is_certified' => $technician->is_certified,
                            'is_verified' => $technician->is_verified,
                            'is_commercial_certification' => $technician->is_commercial_certification,
                            'hvac_license' => $technician->hvac_license,
                            'epa_license' => $technician->epa_license,
                            'first_name' => $technician->first_name,
                            'last_name' => $technician->last_name,
                            'lang' => $technician->lang,
                            'state' => [
                                'short_name' => $technician->state->short_name
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(5, 'data.'.self::QUERY.'.permissions');
    }

    protected function query(): TestResponse
    {
        $query = sprintf(
            'query {
                %s {
                    id
                    is_certified
                    is_verified
                    is_commercial_certification
                    hvac_license
                    epa_license
                    first_name
                    last_name
                    email
                    email_verified_at
                    lang
                    language {
                        name
                        slug
                    }
                    permissions {
                        id
                        name
                    }
                    state {
                        name
                        short_name
                    }
                }
            }',
            self::QUERY
        );

        return $this->postGraphQL(compact('query'));
    }
}
