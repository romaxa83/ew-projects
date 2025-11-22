<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Manuals\Groups;

use App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups\ManualGroupUpdateMutation;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Manuals\ManualGroupTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManualGroupUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ManualGroupUpdateMutation::NAME;

    public function test_update_manual_group(): void
    {
        $this->loginAsSuperAdmin();

        $manualGroup = ManualGroup::factory()
            ->has(
                ManualGroupTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->assertFalse($manualGroup->show_commercial_certified);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'manual_group' => [
                    'id' => $manualGroup->id,
                    'show_commercial_certified' => true,
                    'translations' => [
                        [
                            'title' => $manualGroup->translation->title,
                            'language' => $manualGroup->translation->language,
                        ],
                        [
                            'title' => 'es title',
                            'language' => 'es',
                        ],
                    ],
                ],
            ],
            [
                'id',
                'show_commercial_certified',
                'translations' => [
                    'title',
                    'language'
                ],
            ],
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $manualGroup->id,
                            'show_commercial_certified' => true,
                            'translations' => [
                                [
                                    'title' => $manualGroup->translation->title,
                                    'language' => $manualGroup->translation->language,
                                ],
                                [
                                    'title' => 'es title',
                                    'language' => 'es',
                                ],
                            ],
                        ],
                    ],
                ],
            );
    }
}
