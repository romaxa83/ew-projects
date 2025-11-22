<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Categories;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\GraphQL\Queries\FrontOffice\Catalog\Categories\CategoriesQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoriesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CategoriesQuery::NAME;

    public function test_guest_can_view_categories_list(): void
    {
        $this->assertCanViewCategories();
    }

    public function test_technician_can_view_categories_list(): void
    {
        $this->loginAsTechnicianWithRole();

        $this->assertCanViewCategories();
    }

    protected function getQuery(int $perPage): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'per_page' => $perPage
                ]
            )
            ->select(
                [
                    'data' => [
                        'id'
                    ]
                ]
            )
            ->make();
    }

    public function test_user_can_view_categories_list(): void
    {
        $this->loginAsUserWithRole();

        $this->assertCanViewCategories();
    }

    public function test_different_guards_with_same_model_id_has_access(): void
    {
        $technician = Technician::factory()
            ->certified()
            ->state(
                [
                    'id' => 1
                ]
            )
            ->create();

        $user = User::factory()
            ->state(
                [
                    'id' => 1
                ]
            )
            ->create();

        $this->loginAsUserWithRole($user);
        $this->assertCanViewCategories();

        $this->loginAsTechnicianWithRole($technician);
        $this->assertCanViewCategories();
    }

    protected function assertCanViewCategories(): void
    {
        Product::factory()
            ->count(10)
            ->has(
                Category::factory()
            )
            ->create();

        $this->postGraphQL($this->getQuery(4))
            ->assertOk()
            ->assertJsonCount(4, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id'
                                ]
                            ],
                        ],
                    ]
                ]
            );
    }
}
