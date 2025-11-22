<?php

namespace Tests\Feature\Mutations\FrontOffice\Projects;

use App\Contracts\Members\Member;
use App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectUpdateMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\ProjectCreateTrait;

class MemberProjectUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use ProjectCreateTrait;

    public const MUTATION = MemberProjectUpdateMutation::NAME;

    public function test_user_can_update_project(): void
    {
        $this->assertMemberCanUpdateProject(
            $this->loginAsUserWithRole()
        );
    }

    protected function assertMemberCanUpdateProject(Member $member): void
    {
        $project = $this->createProjectForMember($member);

        /** @var System $system */
        $system = $project->systems->first();

        /** @var Product $unit */
        $unit = $system->units->first();

        $args = $this->getArgs($project, $system, $unit);

        $select = $this->getSelect();

        $query = new GraphQLQuery(self::MUTATION, $args, $select);

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function getArgs(Project $project, System $system, Product $unit): array
    {
        return [
            'project' => [
                'id' => $project->id,
                'name' => 'update project name',
                'systems' => [
                    [
                        'id' => $system->id,
                        'name' => 'updated system name',
                        'description' => 'updated system description',
                        'units' => [
                            [
                                'product_id' => $unit->id,
                                'serial_number' => $unit->unit->serial_number,
                            ]
                        ]
                    ],
                    [
                        'name' => 'new system name',
                        'description' => 'new system description',
                    ]
                ],
            ],
        ];
    }

    protected function getSelect(): array
    {
        return [
            'name',
            'systems' => [
                'id',
                'name',
                'description',
                'units' => [
                    'id',
                    'serial_number',
                ],
            ],
        ];
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                self::MUTATION => [
                    'name',
                    'systems' => [
                        [
                            'id',
                            'name',
                            'description',
                            'units' => [
                                [
                                    'id',
                                    'serial_number',
                                ]
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }

    public function test_technician_can_update_project(): void
    {
        $this->assertMemberCanUpdateProject(
            $this->loginAsTechnicianWithRole()
        );
    }

    public function test_it_can_not_use_used_serial_number(): void
    {
        $user = $this->loginAsUserWithRole();

        $project = $this->createProjectForMember($user);

        /** @var System $system */
        $system = $project->systems->first();
        /** @var Product $unit */
        $unit = $system->units->first();

        $args = $this->getArgs($project, $system, $unit);
        $select = $this->getSelect();
        $query = new GraphQLQuery(self::MUTATION, $args, $select);

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );

        $newSystem = System::factory()
            ->for($project)
            ->create();

        $args = $this->getArgs($project, $newSystem, $unit);
        $query = new GraphQLQuery(self::MUTATION, $args, $select);

        $this->assertResponseHasValidationMessage(
            $this->postGraphQL($query->getMutation()),
            'project.systems.0.units.0.serial_number',
            [
                __(
                    'validation.custom.unit-serial-number-used',
                    ['attribute' => 'project.systems.0.units.0.serial_number']
                )
            ]
        );
    }

    public function test_update_system_with_one_unit_and_many_serials(): void
    {
        $t = $this->loginAsTechnicianWithRole();

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()->times(5),
                'serialNumbers'
            )
            ->create();

        $project = Project::factory()
            ->for($t, 'member')
            ->has(System::factory())
            ->create();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'project' => [
                        'id' => $project->id,
                        'name' => 'project name',
                        'systems' => [
                            [
                                'id' => $project->systems->first()->id,
                                'name' => 'system name',
                                'description' => 'system description',
                                'units' => $product->serialNumbers->toArray()
                            ]
                        ],
                    ]
                ]
            )
            ->select($this->getSelect())
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJsonCount(5, 'data.' . self::MUTATION . '.systems.0.units');
    }

    public function test_member_can_use_serial_once(): void
    {
        $t = $this->loginAsTechnicianWithRole();

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        $project = Project::factory()
            ->for($t, 'member')
            ->has(System::factory())
            ->create();

        $sn = $product->serialNumbers->first()->serial_number;

        $query = $this->createProjectAndGetQuery($t, $product, $sn);

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );

        $query = $this->createProjectAndGetQuery($t, $product, $sn);

        //same serial for same user is not allowed
        $this->assertServerError($this->postGraphQL($query), 'validation');

        $t = $this->loginAsTechnicianWithRole();

        $query = $this->createProjectAndGetQuery($t, $product, $sn);

        //same serial for different user is allowed
        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function createProjectAndGetQuery(
        Technician $technician,
        Product $product,
        string $sn
    ): array {
        $project = Project::factory()
            ->for($technician, 'member')
            ->has(System::factory())
            ->create();

        return GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'project' => [
                        'id' => $project->id,
                        'name' => 'project name',
                        'systems' => [
                            [
                                'id' => $project->systems->first()->id,
                                'name' => 'system name',
                                'description' => 'system description',
                                'units' => [
                                    [
                                        'product_id' => $product->id,
                                        'serial_number' => $sn,
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            )
            ->select($this->getSelect())
            ->make();
    }
}
