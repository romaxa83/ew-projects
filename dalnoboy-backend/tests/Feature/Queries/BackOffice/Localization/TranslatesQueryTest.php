<?php

namespace Tests\Feature\Queries\BackOffice\Localization;

use App\GraphQL\Queries\BackOffice\Localization\TranslatesQuery;
use App\Models\Localization\Translate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TranslatesQueryTest extends TestCase
{
    use DatabaseTransactions;

    /**@var Translate[] $translates */
    private iterable $translates;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        Translate::factory()
            ->count(11)
            ->create();

        $this->translates = Translate::all();
    }

    public function test_get_all_translates(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(TranslatesQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'place',
                            'key',
                            'text',
                            'lang',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TranslatesQuery::NAME => [
                            'data' => $this
                                ->translates
                                ->map(
                                    fn(Translate $translate) => [
                                        'id' => $translate->id,
                                        'place' => $translate->place,
                                        'key' => $translate->key,
                                        'text' => $translate->text,
                                        'lang' => $translate->lang,
                                        'created_at' => $translate->created_at->getTimestamp(),
                                        'updated_at' => $translate->updated_at->getTimestamp(),
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            )
            ->assertJsonCount(11, 'data.' . TranslatesQuery::NAME . '.data');
    }

    public function test_get_filter_by_lang(): void
    {
        $lang = $this->translates[0]->lang;

        $translates = $this
            ->translates
            ->filter(
                fn(Translate $translate) => $translate->lang === $lang
            )
            ->values();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(TranslatesQuery::NAME)
                ->args(
                    [
                        'lang' => [
                            new EnumValue($lang)
                        ]
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TranslatesQuery::NAME => [
                            'data' => $translates
                                ->map(
                                    fn(Translate $translate) => [
                                        'id' => $translate->id,
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            )
            ->assertJsonCount($translates->count(), 'data.' . TranslatesQuery::NAME . '.data');
    }

    public function test_get_filter_by_place(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(TranslatesQuery::NAME)
                ->args(
                    [
                        'place' => [
                            $this->translates[0]->place
                        ]
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TranslatesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->translates[0]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . TranslatesQuery::NAME . '.data');
    }

    public function test_get_filter_by_key(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(TranslatesQuery::NAME)
                ->args(
                    [
                        'query' => $this->translates[2]->key
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TranslatesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->translates[2]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . TranslatesQuery::NAME . '.data');
    }
}
