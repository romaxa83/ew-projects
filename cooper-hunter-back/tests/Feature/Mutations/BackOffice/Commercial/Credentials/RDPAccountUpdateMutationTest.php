<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Credentials;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Mutations\BackOffice\Commercial\Credentials\RDPAccountUpdateMutation;
use App\Models\Commercial\RDPAccount;
use Carbon\Carbon;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RDPAccountUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = RDPAccountUpdateMutation::NAME;

    public function test_update_request_until_time(): void
    {
        $this->loginAsSuperAdmin();

        $account = RDPAccount::factory()
            ->expired()
            ->create();

        $newDate = Carbon::now()
            ->subDays(2)
            ->toDateString();

        self::assertNotEquals($newDate, $account->end_date->format(DatetimeEnum::DATE));

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'id' => $account->id,
                        'end_date' => $newDate,
                    ]
                )
                ->select(
                    [
                        'id',
                        'end_date',
                    ]
                )
                ->make()
        )
            ->assertOk();

        self::assertEquals($newDate, $account->fresh()->end_date->format(DatetimeEnum::DATE));
    }
}
