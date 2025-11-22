<?php

namespace Tests\Feature\Queries\FrontOffice\Stores;

use App\GraphQL\Queries\FrontOffice\Stores\OnlineStoresQuery;
use App\Models\Stores\Store;
use App\Models\Stores\StoreCategory;
use App\Models\Stores\StoreCategoryTranslation;
use App\Models\Stores\StoreTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OnlineStoresQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = OnlineStoresQuery::NAME;

    public function test_get_list_success(): void
    {
        StoreCategory::factory()
            ->times(2)
            ->has(
                Store::factory()
                    ->times(5)
                    ->has(StoreTranslation::factory()->locale(), 'translations')
            )
            ->has(StoreCategoryTranslation::factory()->allLocales(), 'translations')
            ->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                $this->getSelect()
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            $this->getJsonStructure()
                        ]
                    ]
                ]
            );
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'active',
            'translation' => [
                'language',
                'title',
            ],
            'stores' => [
                'id',
                'active',
                'link',
                'translation' => [
                    'language',
                    'title',
                ],
            ],
        ];
    }

    protected function getJsonStructure(): array
    {
        return [
            'id',
            'active',
            'translation' => [
                'language',
                'title',
            ],
            'stores' => [
                [
                    'id',
                    'active',
                    'link',
                    'translation' => [
                        'language',
                        'title',
                    ],
                ]
            ],
        ];
    }
}
