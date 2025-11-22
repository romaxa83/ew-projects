<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Favourites;

use App\GraphQL\Queries\FrontOffice\Favourites\FavouriteProductsQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Favourites\Favourite;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Manuals\ManualGroupTranslation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Videos\Group;
use App\Models\Catalog\Videos\GroupTranslation;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Catalog\Videos\VideoLinkTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Factories\Catalog\Products\ProductFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FavouriteProductsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = FavouriteProductsQuery::NAME;

    public function test_get_favourites(): void
    {
        $user = $this->loginAsUser();

        Favourite::factory()
            ->times(10)
            ->forUser($user)
            ->forProduct($this->getProductFactory())
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'per_page' => 5,
            ],
            [
                'data' => [
                    'favorable' => [
                        'id',
                        'active',
                        'is_favourite',
                        'category' => [
                            'id',
                            'translation' => [
                                'title',
                            ],
                        ],
                        'images' => [
                            'url',
                            'name',
                        ],
                        'video_links' => [
                            'id',
                            'group' => [
                                'id'
                            ],
                        ],
                        'values' => [
                            'id',
                            'feature' => [
                                'id',
                            ],
                        ],
                        'certificates' => [
                            'id',
                            'type_name',
                            'number',
                        ],
                        'manuals' => [
                            'pdf' => [
                                'url',
                            ],
                            'group' => [
                                'translation' => [
                                    'title'
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonCount(5, 'data.' . self::QUERY . '.data')
            ->assertJsonPath('data.' . self::QUERY . '.data.0.favorable.is_favourite', true)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'favorable' => [
                                        'id',
                                        'active',
                                        'category' => [
                                            'id',
                                            'translation' => [
                                                'title'
                                            ],
                                        ],
                                        'video_links' => [
                                            [
                                                'id'
                                            ]
                                        ],
                                        'certificates' => [
                                            [
                                                'id',
                                                'type_name',
                                                'number',
                                            ]
                                        ],
                                        'manuals' => [
                                            [
                                                'pdf' => [
                                                    'url',
                                                ],
                                                'group' => [
                                                    'translation' => [
                                                        'title'
                                                    ],
                                                ],
                                            ]
                                        ],
                                    ]
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }

    protected function getProductFactory(): ProductFactory
    {
        return Product::factory()
            ->for(
                Category::factory()
                    ->has(
                        CategoryTranslation::factory()
                            ->allLocales(),
                        'translations'
                    )
            )
            ->hasAttached(
                VideoLink::factory()
                    ->has(
                        VideoLinkTranslation::factory()
                            ->allLocales(),
                        'translations'
                    )->for(
                        Group::factory()
                            ->has(
                                GroupTranslation::factory()->allLocales(),
                                'translations'
                            )
                    )
            )
            ->hasAttached(
                Certificate::factory()
            )
            ->hasAttached(
                Manual::factory()
                    ->for(
                        ManualGroup::factory()
                            ->has(
                                ManualGroupTranslation::factory()->allLocales(),
                                'translations'
                            ),
                        'group'
                    )
            );
    }
}
