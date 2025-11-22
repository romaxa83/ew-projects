<?php

namespace Tests\Feature\Mutations\FrontOffice\Projects;

use App\Contracts\Members\Member;
use App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectCreateMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberProjectCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberProjectCreateMutation::NAME;

    public function test_user_can_create_project(): void
    {
        $this->assertMemberCanCreateProject(
            $this->loginAsUserWithRole()
        );
    }

    protected function assertMemberCanCreateProject(Member $member): void
    {
        $product = Product::factory()->create();
        $serial = ProductSerialNumber::factory()
            ->state(
                [
                    'product_id' => $product
                ]
            )
            ->create();

        $args = $this->getArgs($product, $serial);

        $select = $this->getSelect();

        $query = new GraphQLQuery(self::MUTATION, $args, $select);

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );

        $this->assertDatabaseCount(Project::TABLE, 1);
        $this->assertDatabaseCount(System::TABLE, 1);

        $this->assertDatabaseHas(
            Project::TABLE,
            [
                'member_type' => $member->getMorphType(),
                'member_id' => $member->getId(),
            ]
        );
    }

    protected function getArgs(Product $product, ProductSerialNumber $serial): array
    {
        return [
            'project' => [
                'name' => 'project name',
                'systems' => [
                    [
                        'name' => 'system name',
                        'description' => 'system description',
                        'units' => [
                            [
                                'product_id' => $product->id,
                                'serial_number' => $serial->serial_number
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }

    protected function getSelect(): array
    {
        return [
            'name',
            'systems' => [
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
                ]
            ]
        ];
    }

    public function test_technician_can_create_project(): void
    {
        $this->assertMemberCanCreateProject(
            $this->loginAsTechnicianWithRole()
        );
    }

    public function test_it_can_not_use_used_serial_number(): void
    {
        $this->loginAsUserWithRole();

        $product = Product::factory()->create();
        $serial = ProductSerialNumber::factory()
            ->state(
                [
                    'product_id' => $product
                ]
            )
            ->create();

        $args = $this->getArgs($product, $serial);
        $select = $this->getSelect();

        $query = new GraphQLQuery(self::MUTATION, $args, $select);

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );

        $this->assertResponseHasValidationMessage(
            $this->postGraphQL($query->getMutation()),
            'project.systems.0.units.0',
            [__('validation.custom.unit-serial-number-used', ['attribute' => 'project.systems.0.units.0'])]
        );
    }

    public function test_create_system_with_one_unit_and_many_serials(): void
    {
        $this->loginAsTechnicianWithRole();

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()->times(5),
                'serialNumbers'
            )
            ->create();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'project' => [
                        'name' => 'project name',
                        'systems' => [
                            [
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
        $this->loginAsTechnicianWithRole();

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        $sn = $product->serialNumbers->first()->serial_number;

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'project' => [
                        'name' => 'project name',
                        'systems' => [
                            [
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

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );

        //same serial for same user is not allowed
        $this->assertServerError($this->postGraphQL($query), 'validation');

        $this->loginAsTechnicianWithRole();

        //same serial for different user is allowed
        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }
}
