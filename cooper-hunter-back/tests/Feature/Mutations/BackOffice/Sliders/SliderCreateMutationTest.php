<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Sliders;

use App\GraphQL\Mutations\BackOffice\Sliders\SliderCreateMutation;
use App\Models\Sliders\Slider;
use App\Models\Sliders\SliderTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SliderCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SliderCreateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertDatabaseCount(Slider::TABLE, 0);
        $this->assertDatabaseCount(SliderTranslation::TABLE, 0);

        $this->mutation($this->getData())
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

        $this->assertDatabaseCount(Slider::TABLE, 1);
        $this->assertDatabaseCount(SliderTranslation::TABLE, 2);
    }

    public function test_create_with_null_fields(): void
    {
        $this->loginAsSuperAdmin();

        $this->mutation(
            [
                'slider' => [
                    'active' => true,
                    'translations' => [
                        [
                            'language' => 'en',
                            'title' => '',
                        ],
                        [
                            'language' => 'es',
                            'description' => '',
                        ],
                    ],
                ]
            ]
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'translation' => [
                                'title',
                                'description',
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'translation' => [
                                'title' => null,
                                'description' => null,
                            ]
                        ]
                    ]
                ]
            );
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

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->mutation($this->getData()), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->mutation($this->getData()), 'Unauthorized');
    }
}
