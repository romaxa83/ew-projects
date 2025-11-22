<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Stores\Stores;

use App\GraphQL\Mutations\BackOffice\Stores\Stores\StoreCreateMutation;
use App\Models\Stores\StoreCategory;
use App\Models\Stores\StoreCategoryTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = StoreCreateMutation::NAME;

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

    protected function getMutation(array $args = []): TestResponse
    {
        $id = StoreCategory::factory()
            ->has(StoreCategoryTranslation::factory()->allLocales(), 'translations')
            ->create()
            ->id;

        return $this->mutation(
            array_merge($this->getArgs($id), $args),
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

    protected function getArgs(int $id): array
    {
        return [
            'input' => [
                'active' => true,
                'store_category_id' => $id,
                'link' => $this->faker->imageUrl,
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
        ];
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'active',
            'link',
            'category' => [
                'id',
                'active',
                'translation' => [
                    'language',
                    'title',
                ]
            ],
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
