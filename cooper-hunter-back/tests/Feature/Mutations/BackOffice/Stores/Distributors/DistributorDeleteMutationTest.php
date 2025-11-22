<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Stores\Distributors;

use App\GraphQL\Mutations\BackOffice\Stores\Distributors\DistributorDeleteMutation;
use App\Models\Stores\Distributor;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DistributorDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DistributorDeleteMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $this->getMutation()
            ->assertJsonPath('data.' . self::MUTATION . '.type', 'success');
    }

    protected function getMutation(): TestResponse
    {
        $id = Distributor::factory()->create()->id;

        return $this->mutation(compact('id'));
    }

    protected function mutation(array $args): TestResponse
    {
        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args($args)
            ->select(['type']);

        return $this->postGraphQLBackOffice($query->make());
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
