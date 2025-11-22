<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Filters;

use App\GraphQL\Queries\FrontOffice\Catalog\Filters\FilterQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\FeatureTranslation;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FilterQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = FilterQuery::NAME;

    public function test_get_filters_with_values(): void
    {
        $features = $this->getFeatures();

        $valueIds = $features->pluck('values')->flatten()->pluck('id');

        $product = Product::factory()
            ->for($category = Category::factory()->create())
            ->create();

        $product->values()->sync($valueIds);

        $query = $this->getQuery(
            [
                'category_id' => $category->id
            ]
        );

        $this->assertSeeFilters($query);
    }

    /**
     * @return Collection<Feature>
     */
    protected function getFeatures(): Collection
    {
        return Feature::factory()
            ->times(3)
            ->has(
                FeatureTranslation::factory()->allLocales(),
                'translations'
            )
            ->has(
                Value::factory()
                    ->times(3)
            )
            ->create();
    }

    protected function getQuery(array $args): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select(
                [
                    'feature_name',
                    'feature_short_name',
                    'values' => [
                        'id',
                        'name',
                    ],
                ]
            )
            ->make();
    }

    protected function assertSeeFilters(array $query): void
    {
        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'feature_name',
                                'feature_short_name',
                                'values' => [
                                    [
                                        'id',
                                        'name',
                                    ]
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }

    public function test_empty_features_list(): void
    {
        $this->getFeatures();

        $query = $this->getQuery(
            [
                'category_id' => Category::factory()->create()->id
            ]
        );

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(0, 'data.filter');
    }

    public function test_features_for_search(): void
    {
        $features = $this->getFeatures();

        $valueIds = $features->pluck('values')->flatten()->pluck('id');

        $product = Product::factory()
            ->for(Category::factory()->create())
            ->state(
                [
                    'title' => 'title12345'
                ]
            )
            ->create();

        $product->values()->sync($valueIds);

        $query = $this->getQuery(
            [
                'search_query' => 'title1'
            ]
        );

        $this->assertSeeFilters($query);
    }
}
