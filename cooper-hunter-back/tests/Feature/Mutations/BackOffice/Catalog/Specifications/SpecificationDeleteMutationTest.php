<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Catalog\Specifications;

use App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications\SpecificationDeleteMutation;
use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Features\SpecificationTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SpecificationDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SpecificationDeleteMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $s = Specification::factory()
            ->has(SpecificationTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->assertDatabaseCount(SpecificationTranslation::TABLE, 2);

        $this->mutation(['id' => $s->id])
            ->assertOk();

        $this->assertModelMissing($s);
        $this->assertDatabaseCount(SpecificationTranslation::TABLE, 0);
    }

    protected function mutation(array $args): TestResponse
    {
        $query = new GraphQLQuery(
            self::MUTATION,
            $args,
            [
                'message'
            ]
        );

        return $this->postGraphQLBackOffice($query->getMutation());
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();
        $s = Specification::factory()->create();

        $this->assertServerError($this->mutation(['id' => $s->id]), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $s = Specification::factory()->create();

        $this->assertServerError($this->mutation(['id' => $s->id]), 'Unauthorized');
    }
}
