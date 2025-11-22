<?php

namespace Tests\Feature\Mutations\FrontOffice\Dictionaries\TireSizes;

use App\GraphQL\Mutations\FrontOffice\Dictionaries\TireSizes\TireSizeCreateMutation;
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

        $this->loginAsUserWithRole();
    }

    public function test_create_tire_size(): void
    {
        $height = TireHeight::factory()->create();
        $diameter = TireDiameter::factory()
            ->create();
        $width = TireWidth::factory()
            ->create();
        $tireSizeData = [
            'tire_height_id' => $height->getKey(),
            'tire_diameter_id' => $diameter->getKey(),
            'tire_width_id' => $width->getKey(),
        ];

        $tireSizeId = $this->postGraphQL(
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
                'is_moderated' => false,
            ]
        );
    }

    public function test_create_same_tire_size(): void
    {
        $height = TireHeight::factory()->create();
        $diameter = TireDiameter::factory()
            ->create();
        $width = TireWidth::factory()
            ->create();
        TireSize::factory()
            ->create(
                [
                    'tire_height_id' => $height->getKey(),
                    'tire_diameter_id' => $diameter->getKey(),
                    'tire_width_id' => $width->getKey(),
                ]
            );

        $tireSizeData = [
            'tire_height_id' => $height->getKey(),
            'tire_diameter_id' => $diameter->getKey(),
            'tire_width_id' => $width->getKey(),
        ];

        $this->postGraphQL(
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
                            'message' => trans('validation.custom.same_entity_exists'),
                        ]
                    ]
                ]
            );
    }

    public function test_create_same_tire_size_in_offline(): void
    {
        $height = TireHeight::factory()->create();
        $diameter = TireDiameter::factory()
            ->create();
        $width = TireWidth::factory()
            ->create();

        $size = TireSize::factory()->create(
            [
                'tire_height_id' => $height->getKey(),
                'tire_diameter_id' => $diameter->getKey(),
                'tire_width_id' => $width->getKey(),
            ]);

        TireSize::factory()->create(
            [
                'tire_height_id' => $height->getKey(),
                'tire_diameter_id' => $diameter->getKey(),
                'tire_width_id' => $width->getKey(),
            ]);

        $tireSizeData = [
            'tire_height_id' => $height->getKey(),
            'tire_diameter_id' => $diameter->getKey(),
            'tire_width_id' => $width->getKey(),
            'is_offline' => true,
        ];

        $tireSizeId = $this->postGraphQL(
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
            ->json('data.' . TireSizeCreateMutation::NAME . '.id');

        $this->assertEquals($size->id, $tireSizeId);
    }
}
