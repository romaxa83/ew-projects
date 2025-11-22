<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\BackOffice\Stores;

use App\GraphQL\Queries\BackOffice\Stores\StoreQuery;
use App\Models\Stores\Store;
use App\Models\Stores\StoreCategory;
use App\Models\Stores\StoreTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = StoreQuery::NAME;

    public function test_get_list_success(): void
    {
        $this->loginAsSuperAdmin();

        $category = StoreCategory::factory()
            ->has(
                Store::factory()
                    ->times(5)
                    ->has(StoreTranslation::factory()->locale(), 'translations')
            )
            ->create();

        $this->query(
            [
                'store_category_id' => $category->id,
            ]
        )
            ->assertJsonCount(5, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            $this->getSelect()
                        ]
                    ]
                ]
            );
    }

    protected function query(array $args = []): TestResponse
    {
        $query = GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select(
                $this->getSelect()
            );

        return $this->postGraphQLBackOffice($query->make());
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'active',
            'link',
            'translation' => [
                'title',
            ],
        ];
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $category = StoreCategory::factory()->create();

        $this->assertServerError(
            $this->query(
                [
                    'store_category_id' => $category->id,
                ]
            ),
            'No permission'
        );
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $category = StoreCategory::factory()->create();

        $this->assertServerError(
            $this->query(
                [
                    'store_category_id' => $category->id,
                ]
            ),
            'Unauthorized'
        );
    }
}
