<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Search;

use App\GraphQL\Queries\FrontOffice\Catalog\Search\SearchUnionQuery;
use App\GraphQL\Types\Catalog\Categories\CategoryRootType;
use App\GraphQL\Types\Catalog\Categories\CategoryType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class SearchTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;
    use WithFaker;

    protected ProductBuilder $builderProduct;
    protected CategoryBuilder $builderCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builderProduct = app(ProductBuilder::class);
        $this->builderCategory = app(CategoryBuilder::class);
    }

    /** @test */
    public function search_product_and_category(): void
    {
        $title = "Search";
        // product
        $this->builderProduct->withTranslation()
            ->setId(242)
            ->setTitle($title)->create();
        $this->builderProduct->withTranslation()->create();

        // category root
        $this->builderCategory->withTranslation()
            ->setId(242)
            ->setTitle($title)->create();
        $parentCategory = $this->builderCategory->withTranslation()->create();

        // category
        $this->builderCategory->withTranslation()
            ->setParentId($parentCategory->id)
            ->setTitle($title)->create();
        $this->builderCategory->withTranslation()
            ->setParentId($parentCategory->id)
            ->setTitle($title . "BLA")->create();
        $this->builderCategory->setParentId($parentCategory->id)->withTranslation()->create();
        $this->builderCategory->setParentId($parentCategory->id)->withTranslation()->create();

        $this->loginAsTechnicianWithRole();

        $result = collect(
            $this->postGraphQL(['query' => $this->getQueryStr($title)])
                ->assertJsonCount(4, 'data.' . SearchUnionQuery::NAME)
                ->json('data.' . SearchUnionQuery::NAME)
        );

        $this->assertEquals(2, $result->filter(
            fn(array $item) => $item['__typename'] === CategoryType::NAME
        )->count());

        $this->assertEquals(1, $result->filter(
            fn(array $item) => $item['__typename'] === CategoryRootType::NAME
        )->count());

        $this->assertEquals(1, $result->filter(
            fn(array $item) => $item['__typename'] === ProductType::NAME
        )->count());
    }

    /** @test */
    public function search_product_and_root_category(): void
    {
        $title = "Search";
        // product
        $this->builderProduct->withTranslation()
            ->setTitle($title)->create();
        $this->builderProduct->withTranslation()->create();
        $this->builderProduct->withTranslation()->create();

        // category root
        $this->builderCategory->withTranslation()
            ->setTitle("Bla " . $title)->create();
        $parentCategory = $this->builderCategory->withTranslation()->create();

        // category
        $this->builderCategory->setParentId($parentCategory->id)->withTranslation()->create();
        $this->builderCategory->setParentId($parentCategory->id)->withTranslation()->create();

        $this->loginAsTechnicianWithRole();

        $result = collect(
            $this->postGraphQL(['query' => $this->getQueryStr($title)])
                ->assertJsonCount(2, 'data.' . SearchUnionQuery::NAME)
                ->json('data.' . SearchUnionQuery::NAME)
        );

        $this->assertEquals(0, $result->filter(
            fn(array $item) => $item['__typename'] === CategoryType::NAME
        )->count());

        $this->assertEquals(1, $result->filter(
            fn(array $item) => $item['__typename'] === CategoryRootType::NAME
        )->count());

        $this->assertEquals(1, $result->filter(
            fn(array $item) => $item['__typename'] === ProductType::NAME
        )->count());
    }

    /** @test */
    public function search_only_product(): void
    {
        $title = "Search";
        // product
        $this->builderProduct->withTranslation()
            ->setTitle($title)->create();
        $this->builderProduct->withTranslation()->create();
        $this->builderProduct->withTranslation()->create();

        // category root
        $parentCategory = $this->builderCategory->withTranslation()->create();

        // category
        $this->builderCategory->setParentId($parentCategory->id)->withTranslation()->create();
        $this->builderCategory->setParentId($parentCategory->id)->withTranslation()->create();

        $this->loginAsTechnicianWithRole();

        $result = collect(
            $this->postGraphQL(['query' => $this->getQueryStr($title)])
                ->assertJsonCount(1, 'data.' . SearchUnionQuery::NAME)
                ->json('data.' . SearchUnionQuery::NAME)
        );

        $this->assertEquals(0, $result->filter(
            fn(array $item) => $item['__typename'] === CategoryType::NAME
        )->count());

        $this->assertEquals(0, $result->filter(
            fn(array $item) => $item['__typename'] === CategoryRootType::NAME
        )->count());

        $this->assertEquals(1, $result->filter(
            fn(array $item) => $item['__typename'] === ProductType::NAME
        )->count());
    }

    /** @test */
    public function search_fail_small_query(): void
    {
        $title = "S";
        // product
        $this->builderProduct->withTranslation()
            ->setTitle($title)->create();
        $this->builderProduct->withTranslation()->create();
        $this->builderProduct->withTranslation()->create();

        $this->loginAsTechnicianWithRole();

        $res = $this->postGraphQL(['query' => $this->getQueryStr($title)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    protected function getQueryStr(string $value): string
    {
        return sprintf(
            '
                query {
                    %s (query: "%s")
                    {
                        __typename ... on %s {
                            id,
                            title
                        }
                        __typename ... on %s {
                            id,
                            categoryTranslations: translations {
                                title
                            }
                        }
                        __typename ... on %s {
                            id,
                            categoryTranslations: translations {
                                title
                            }
                        }
                    }
                }',
            SearchUnionQuery::NAME,
            $value,
            ProductType::NAME,
            CategoryType::NAME,
            CategoryRootType::NAME
        );
    }
}
