<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Stores\StoreCategories;

use App\GraphQL\Mutations\BackOffice\Stores\StoreCategories\StoreCategoryCreateMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreCategoryCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = StoreCategoryCreateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $this->getMutation()
            ->assertJsonStructure(
                [
                    'data' => [
                        static::MUTATION => $this->getSelect(),
                    ],
                ]
            );
    }

    protected function getMutation(array $merge = []): TestResponse
    {
        return $this->mutation(
            $this->getArgs($merge),
            $this->getSelect()
        );
    }

    protected function mutation(array $args, array $select): TestResponse
    {
        $query = GraphQLQuery::mutation(static::MUTATION)
            ->args($args)
            ->select($select);

        return $this->postGraphQLBackOffice($query->make());
    }

    protected function getArgs(array $merge): array
    {
        return array_merge(
            [
                'input' => [
                    'active' => true,
                    'translations' => [
                        [
                            'language' => 'en',
                            'title' => 'en title',
                        ],
                        [
                            'language' => 'es',
                            'title' => 'es title',
                        ],
                    ],
                ],
            ],
            $merge,
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
            ]
        ];
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->getMutation(), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->getMutation(), 'Unauthorized');
    }
}
