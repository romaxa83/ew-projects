<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Credentials;

use App\Enums\Commercial\CommercialCredentialsStatusEnum;
use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Mutations\BackOffice\Commercial\Credentials\CredentialsRequestApproveMutation;
use App\Models\Commercial\CredentialsRequest;
use App\Models\Commercial\RDPAccount;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CredentialsRequestApproveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CredentialsRequestApproveMutation::NAME;

    public function test_approve_credentials_request(): void
    {
        $this->loginAsSuperAdmin();

        $request = CredentialsRequest::factory()
            ->forTechnician(
                Technician::factory()
                    ->state(
                        [
                            'email' => 'fedorgrechaniy@gmail.com',
                            'first_name' => 'fedor',
                            'last_name' => 'grecha',
                        ]
                    )
            )
            ->create();

        $this->assertDatabaseCount(RDPAccount::TABLE, 0);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'credentials_request_id' => $request->id,
                        'end_date' => now()->addMonth()->format(DatetimeEnum::DATE)
                    ]
                )
                ->make()
        )
            ->assertJsonPath('data.' . self::MUTATION, true);

        $request = $request->fresh();

        self::assertTrue($request->status->is(CommercialCredentialsStatusEnum::APPROVED()));

        $this->assertDatabaseCount(RDPAccount::TABLE, 1);
    }
}