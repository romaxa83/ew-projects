<?php

namespace Tests\Feature\Queries\FrontOffice\Localization;

use App\GraphQL\Queries\FrontOffice\Localization\TranslatesListQuery;
use App\Models\Localization\Translate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslatesListQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**@var Translate[] $translates */
    private iterable $translates;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();

        Translate::factory(
            [
                'lang' => $this->faker->language,
                'place' => $this->faker->bothify,
            ]
        )
            ->count(11)
            ->create();

        $this->translates = Translate::all();
    }

    public function test_get_all_translates(): void
    {
        $this->postGraphQL(
            GraphQLQuery::query(TranslatesListQuery::NAME)
                ->args(
                    [
                        'sort' => [
                            'key-asc'
                        ],
                        'lang' => [
                            new EnumValue($this->translates[0]->lang)
                        ],
                        'place' => [
                            $this->translates[0]->place
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'place',
                        'key',
                        'text',
                        'lang',
                        'created_at',
                        'updated_at'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TranslatesListQuery::NAME => $this
                            ->translates
                            ->sortBy('key')
                            ->values()
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
            )
            ->assertJsonCount(11, 'data.' . TranslatesListQuery::NAME);
    }
}
