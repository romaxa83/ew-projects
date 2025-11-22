<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\BackOffice\Stores;

use App\GraphQL\Queries\BackOffice\Stores\StoreCategoryQuery;
use App\Models\Stores\Store;
use App\Models\Stores\StoreCategory;
use App\Models\Stores\StoreCategoryTranslation;
use App\Models\Stores\StoreTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreCategoryQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = StoreCategoryQuery::NAME;

    public function test_get_list_success(): void
    {
        $this->loginAsSuperAdmin();

        StoreCategory::factory()
            ->has(StoreCategoryTranslation::factory()->locale(), 'translations')
            ->has(
                Store::factory()
                    ->times(5)
                    ->has(StoreTranslation::factory()->locale(), 'translations')
            )
            ->create();

        $this->query()
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
            'translation' => [
                'title',
            ],
            'stores' => [
                'id',
                'link',
            ],
        ];
    }

    protected function getJsonStructure(): array
    {
        return [
            'id',
            'active',
            'translation' => [
                'title',
            ],
            'stores' => [
                [
                    'id',
                    'link',
                ]
            ],
        ];
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->query(), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->query(), 'Unauthorized');
    }
}
