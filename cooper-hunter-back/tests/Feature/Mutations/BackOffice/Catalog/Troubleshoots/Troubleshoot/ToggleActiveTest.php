<?php

namespace Feature\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootToggleActiveMutation;
use App\Models\Catalog\Troubleshoots;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class ToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Troubleshoot\UpdatePermission::KEY]);
    }

    public function test_success(): void
    {
        $troubleshoot = Troubleshoots\Troubleshoot::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TroubleshootToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $troubleshoot->id
                    ]
                )
                ->select([
                    'id',
                    'active',
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    TroubleshootToggleActiveMutation::NAME => [
                        'id' => $troubleshoot->id,
                        'active' => false,
                    ]
                ]
            ]);
    }
}


