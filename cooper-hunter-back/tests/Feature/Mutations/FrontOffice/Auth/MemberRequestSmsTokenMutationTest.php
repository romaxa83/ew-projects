<?php

namespace Tests\Feature\Mutations\FrontOffice\Auth;

use App\GraphQL\Mutations\FrontOffice\Auth\MemberRequestSmsTokenMutation;
use App\Models\Auth\MemberPhoneVerification;
use App\Notifications\Members\MemberPhoneVerificationSms;
use Core\Facades\Sms;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberRequestSmsTokenMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberRequestSmsTokenMutation::NAME;

    public function test_send_sms_code(): void
    {
        Sms::fake();

        $this->loginAsTechnicianWithRole();

        $phone = '123456789';

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'phone_number' => $phone,
            ],
            [
                'token',
                'expires_at'
            ]
        );

        $response = $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'token',
                            'expires_at',
                        ],
                    ],
                ]
            )
            ->json('data.' . self::MUTATION);

        $this->assertDatabaseHas(
            MemberPhoneVerification::TABLE,
            [
                'phone' => $phone,
                'sms_token' => $response['token']
            ],
        );

        $this->assertDatabaseCount(MemberPhoneVerification::TABLE, 1);

        Sms::assertQueued(MemberPhoneVerificationSms::class);
    }
}
