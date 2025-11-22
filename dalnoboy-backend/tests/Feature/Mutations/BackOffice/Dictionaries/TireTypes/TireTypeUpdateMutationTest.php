<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes\TireTypeUpdateMutation;
use App\Models\Dictionaries\TireType;
use App\Models\Dictionaries\TireTypeTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireTypeUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_type(): void
    {
        $tireType = TireType::factory()->create();

        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $tireTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireType->id,
                        'tire_type' => $tireTypeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk();

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                TireTypeTranslate::class,
                [
                    'row_id' => $tireType->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }

    public function test_update_tire_type_only_with_default_language(): void
    {
        $tireType = TireType::factory()->create();

        $translates = [];
        foreach (languages() as $language) {
            if (!$language->default) {
                continue;
            }
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $tireTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireType->id,
                        'tire_type' => $tireTypeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk();

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                TireTypeTranslate::class,
                [
                    'row_id' => $tireType->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }
    public function test_empty_default_language(): void
    {
        $tireType = TireType::factory()->create();

        $translates = [];
        foreach (languages() as $language) {
            if ($language->default) {
                continue;
            }
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $tireTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireType->id,
                        'tire_type' => $tireTypeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'validation'
                        ]
                    ]
                ]
            );
    }

}
