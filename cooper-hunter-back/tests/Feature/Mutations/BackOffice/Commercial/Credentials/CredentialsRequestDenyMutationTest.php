<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Credentials;

use App\Enums\Commercial\CommercialCredentialsStatusEnum;
use App\GraphQL\Mutations\BackOffice\Commercial\Credentials\CredentialsRequestDenyMutation;
use App\Models\Commercial\CredentialsRequest;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CredentialsRequestDenyMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CredentialsRequestDenyMutation::NAME;

    public function test_approve_credentials_request(): void
    {
        $this->loginAsSuperAdmin();

        $request = CredentialsRequest::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'credentials_request_id' => $request->id,
                    ]
                )
                ->make()
        )
            ->assertJsonPath('data.' . self::MUTATION, true);

        $request = $request->fresh();

        self::assertTrue($request->status->is(CommercialCredentialsStatusEnum::DENIED()));
    }
}