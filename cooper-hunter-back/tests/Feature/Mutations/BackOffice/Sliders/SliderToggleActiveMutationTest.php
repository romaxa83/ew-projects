<?php

namespace Tests\Feature\Mutations\BackOffice\Sliders;

use App\GraphQL\Mutations\BackOffice\Sliders\SliderToggleActiveMutation;
use App\Models\Sliders\Slider;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SliderToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SliderToggleActiveMutation::NAME;

    public function test_toggle_active(): void
    {
        $this->loginAsSuperAdmin();

        $slider = Slider::factory()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $slider->id,
            ],
            [
                'id',
                'active',
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonPath('data.' . self::MUTATION . '.active', false)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'active',
                        ],
                    ],
                ]
            );
    }
}
