<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireSizes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes\TireSizeUpdateMutation;
use App\Models\Dictionaries\TireDiameter;
use App\Models\Dictionaries\TireHeight;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireWidth;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireSizeUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_size(): void
    {
        $tireSize = TireSize::factory()->create();

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

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSizeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireSize->id,
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
            ->assertOk();

        $this->assertDatabaseHas(
            TireSize::class,
            [
                'id' => $tireSize->id,
                'active' => true,
                'tire_height_id' => $height->getKey(),
                'tire_diameter_id' => $diameter->getKey(),
                'tire_width_id' => $width->getKey(),
            ]
        );
    }

    public function test_empty_height(): void
    {
        $tireSize = TireSize::factory()->create();

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
            GraphQLQuery::mutation(TireSizeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireSize->id,
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
