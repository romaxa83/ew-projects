<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireSizes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes\TireSizeCreateMutation;
use App\Models\Dictionaries\TireDiameter;
use App\Models\Dictionaries\TireHeight;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireWidth;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireSizeCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_size(): void
    {
        $height = TireHeight::factory()->create();
        $diameter = TireDiameter::factory()
            ->create();
        $width = TireWidth::factory()
            ->create();
        $tireSizeData = [
            'active' => true,
            'tire_height_id' => $height->getKey(),
            'tire_diameter_id' => $diameter->getKey(),
            'tire_width_id' => $width->getKey(),
        ];

        $tireSizeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSizeCreateMutation::NAME)
                ->args(
                    [
                        'tire_size' => $tireSizeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'tire_height' => ['id'],
                        'tire_diameter' => ['id'],
                        'tire_width' => ['id'],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        TireSizeCreateMutation::NAME => [
                            'id',
                            'active',
                            'tire_height' => ['id'],
                            'tire_diameter' => ['id'],
                            'tire_width' => ['id'],
                        ]
                    ]
                ]
            )
            ->json('data.' . TireSizeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireSize::class,
            [
                'id' => $tireSizeId,
                'active' => true,
                'tire_height_id' => $height->getKey(),
                'tire_diameter_id' => $diameter->getKey(),
                'tire_width_id' => $width->getKey(),
            ]
        );
    }

    public function test_create_tire_size_with_empty_width(): void
    {
        $height = TireHeight::factory()
            ->create();
        $diameter = TireDiameter::factory()
            ->create();
        $tireSizeData = [
            'active' => true,
            'tire_height_id' => $height->getKey(),
            'tire_diameter_id' => $diameter->getKey(),
        ];

        $tireSizeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSizeCreateMutation::NAME)
                ->args(
                    [
                        'tire_size' => $tireSizeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'tire_height' => ['id'],
                        'tire_diameter' => ['id'],
                        'tire_width' => ['id'],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        TireSizeCreateMutation::NAME => [
                            'id',
                            'active',
                            'tire_height' => ['id'],
                            'tire_diameter' => ['id'],
                            'tire_width' => ['id'],
                        ]
                    ]
                ]
            )
            ->json('data.' . TireSizeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireSize::class,
            [
                'id' => $tireSizeId,
                'active' => true,
                'tire_height_id' => $height->getKey(),
                'tire_diameter_id' => $diameter->getKey(),
                'tire_width_id' => null,
            ]
        );
    }

    public function test_empty_height(): void
    {
        $diameter = TireDiameter::factory()
            ->create();
        $width = TireWidth::factory()
            ->create();
        $tireSizeData = [
            'active' => true,
            'tire_diameter_id' => $diameter->getKey(),
            'tire_width_id' => $width->getKey(),
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSizeCreateMutation::NAME)
                ->args(
                    [
                        'tire_size' => $tireSizeData
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field TireSizeInputType.tire_height_id of required type ID! was not provided.'
                        ]
                    ]
                ]
            );
    }
}
