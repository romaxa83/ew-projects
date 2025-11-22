<?php

namespace Feature\Mutations\BackOffice\Catalog\Products;

use App\GraphQL\Mutations\BackOffice\Catalog\Products\ProductDeleteMutation;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products;
use Core\Enums\Messages\MessageTypeEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Products\DeletePermission::KEY]);
    }

    public function test_success(): void
    {
        $product = Product::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProductDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $product->id
                    ]
                )
                ->select(
                    [
                        'message',
                        'type'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    ProductDeleteMutation::NAME => [
                        'message' => __('messages.catalog.product.actions.delete.success.one-entity'),
                        'type' => MessageTypeEnum::SUCCESS
                    ]
                ]
            ]);
    }
}

