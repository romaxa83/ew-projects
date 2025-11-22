<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Regulations;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations\RegulationUpdateMutation;
use App\Models\Dictionaries\Regulation;
use App\Models\Dictionaries\RegulationTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegulationUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_regulation(): void
    {
        $regulation = Regulation::factory()->create();

        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $regulationData = [
            'active' => true,
            'days' => 10,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $regulation->id,
                        'regulation' => $regulationData
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
                RegulationTranslate::class,
                [
                    'row_id' => $regulation->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }

    public function test_update_regulation_only_with_default_language(): void
    {
        $regulation = Regulation::factory()->create();

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

        $regulationData = [
            'active' => true,
            'distance' => 40,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $regulation->id,
                        'regulation' => $regulationData
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
                RegulationTranslate::class,
                [
                    'row_id' => $regulation->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }
    public function test_empty_default_language(): void
    {
        $regulation = Regulation::factory()->create();

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

        $regulationData = [
            'active' => true,
            'days' => 10,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $regulation->id,
                        'regulation' => $regulationData
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
