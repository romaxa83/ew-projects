<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Manuals\Groups;

use App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups\ManualGroupCreateMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManualGroupCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ManualGroupCreateMutation::NAME;

    public function test_create_manual_group(): void
    {
        $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'manual_group' => [
                    'show_commercial_certified' => true,
                    'translations' => [
                        [
                            'title' => 'en title',
                            'language' => 'en',
                        ],
                        [
                            'title' => 'es title',
                            'language' => 'es',
                        ]
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
                            'show_commercial_certified' => true,
                            'translations' => [
                                [
                                    'title' => 'en title',
                                    'language' => 'en',
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

    public function test_incomplete_translations_error(): void
    {
        $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'manual_group' => [
                    'translations' => [
                        [
                            'title' => 'en title',
                            'language' => 'en',
                        ]
                    ],
                ],
            ],
            [
                'id',
                'translations' => [
                    'title',
                    'language'
                ],
            ],
        );

        $this->assertResponseHasValidationMessage(
            $this->postGraphQLBackOffice($query->getMutation()),
            'manual_group.translations',
            [
                __('validation.translates_array_validation_failed')
            ],
        );
    }
}
