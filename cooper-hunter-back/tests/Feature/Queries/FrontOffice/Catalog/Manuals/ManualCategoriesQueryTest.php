<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Manuals;

use App\GraphQL\Queries\FrontOffice\Catalog\Manuals\ManualCategoriesQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Manuals\ManualGroupTranslation;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ManualCategoriesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = ManualCategoriesQuery::NAME;

    public function test_get_categories_which_has_manuals(): void
    {
        $this->buildCategoriesWithManuals();

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    'category_name',
                    'category_id',
                    'sub_categories' => [
                        'category_id',
                        'category_name',
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'category_name',
                                'category_id',
                                'sub_categories' => [
                                    [
                                        'category_id',
                                        'category_name',
                                    ]
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }

    protected function buildCategoriesWithManuals(): Collection
    {
        $c = Category::factory()
            ->times(2)
            ->has(CategoryTranslation::factory()->allLocales(), 'translations')
            ->has(
                Category::factory()
                    ->times(2)
                    ->has(CategoryTranslation::factory()->allLocales(), 'translations'),
                'children'
            )
            ->create();

        $this->createProductForCategory(
            Category::factory()
                ->has(CategoryTranslation::factory()->allLocales(), 'translations')
                ->create()
        );

        foreach ($c->pluck('children') as $children) {
            foreach ($children as $child) {
                $this->createProductForCategory($child);
            }
        }

        return $c;
    }

    protected function createProductForCategory($child): void
    {
        Product::factory()
            ->for($child)
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
            )
            ->create();
    }
}
