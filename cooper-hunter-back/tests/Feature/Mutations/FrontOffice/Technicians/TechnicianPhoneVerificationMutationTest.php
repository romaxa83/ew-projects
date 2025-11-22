<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianPhoneVerificationMutation;
use App\Models\Auth\MemberPhoneVerification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TechnicianPhoneVerificationMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = TechnicianPhoneVerificationMutation::NAME;

    public function test_verify_phone(): void
    {
        $user = $this->loginAsTechnician();

        $code = MemberPhoneVerification::factory()->withAccessToken()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'sms_access_token' => $code->access_token,
            ],
        );

        $this->postGraphQL($query->getMutation())
            ->assertJsonPath('data.' . self::MUTATION, true);

        self::assertEquals($code->phone, $user->fresh()->phone);
    }
}
