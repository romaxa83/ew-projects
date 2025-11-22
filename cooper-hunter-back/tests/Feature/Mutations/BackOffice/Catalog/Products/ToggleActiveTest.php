<?php

namespace Feature\Mutations\BackOffice\Catalog\Products;

use App\GraphQL\Mutations\BackOffice\Catalog\Products\ProductToggleActiveMutation;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class ToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Products\UpdatePermission::KEY]);
    }

    /** @test */
    public function test_success(): void
    {
        $product = Product::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProductToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $product->id
                    ]
                )
                ->select(
                    [
                        'id',
                        'active'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    ProductToggleActiveMutation::NAME => [
                        'id' => $product->id,
                        'active' => false
                    ]
                ]
            ]);
    }
}

