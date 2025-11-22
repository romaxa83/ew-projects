<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Sliders;

use App\GraphQL\Mutations\BackOffice\Sliders\SliderUpdateMutation;
use App\Models\Sliders\Slider;
use App\Models\Sliders\SliderTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SliderUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SliderUpdateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $slider = Slider::factory()
            ->has(
                SliderTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->create();

        $data = $this->getData();
        $data['slider_id'] = $slider->id;

        $this->mutation($data)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id'
                        ],
                    ]
                ]
            );
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $slider = Slider::factory()
            ->has(SliderTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getData();
        $data['slider_id'] = $slider->id;

        $this->assertServerError($this->mutation($data), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $slider = Slider::factory()
            ->has(
                SliderTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->create();

        $data = $this->getData();
        $data['slider_id'] = $slider->id;

        $this->assertServerError($this->mutation($data), 'Unauthorized');
    }

    protected function getData(): array
    {
        return [
            'slider' => [
                'active' => true,
                'translations' => [
                    [
                        'language' => 'en',
                        'title' => 'en title',
                        'description' => 'en description',
                    ],
                    [
                        'language' => 'es',
                        'title' => 'es title',
                        'description' => 'es description',
                    ],
                ],
            ]
        ];
    }

    protected function mutation(array $args): TestResponse
    {
        $query = GraphQLQuery::mutation(
            self::MUTATION
        )
            ->args($args)
            ->select(
                [
                    'id',
                    'translation' => [
                        'title',
                        'description',
                    ],
                ]
            );

        return $this->postGraphQLBackOffice($query->make());
    }
}
