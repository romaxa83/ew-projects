<?php

namespace Tests\Feature\Mutations\FrontOffice\Projects;

use App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectSystemUpdateMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Projects\System;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Catalog\ProductSerialNumberBuilder;
use Tests\Builders\Projects\ProjectBuilder;
use Tests\Builders\Projects\SystemBuilder;
use Tests\TestCase;
use Tests\Traits\Models\ProjectCreateTrait;

class MemberProjectSystemUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use ProjectCreateTrait;

    public const MUTATION = MemberProjectSystemUpdateMutation::NAME;

    protected ProjectBuilder $projectBuilder;
    protected SystemBuilder $systemBuilder;
    protected ProductBuilder $productBuilder;
    protected ProductSerialNumberBuilder $productSerialNumberBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->systemBuilder = resolve(SystemBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->productSerialNumberBuilder = resolve(ProductSerialNumberBuilder::class);
    }

    public function test_user_can_update_system(): void
    {
        $project = $this->createProjectForMember(
            $this->loginAsUserWithRole()
        );

        /** @var System $system */
        $system = $project->systems->first();

        /** @var Product $unit */
        $unit = $system->units->first();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'name' => 'updated system name',
                    'description' => 'updated system description',
                    'units' => [
                        [
                            'product_id' => $unit->id,
                            'serial_number' => $unit->unit->serial_number,
                        ]
                    ]
                ]
            ],
            [
                'id',
                'name',
                'description',
                'units' => [
                    'id',
                    'serial_number',
                ],
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'name',
                            'description',
                            'units' => [
                                [
                                    'id',
                                    'serial_number',
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }
}
