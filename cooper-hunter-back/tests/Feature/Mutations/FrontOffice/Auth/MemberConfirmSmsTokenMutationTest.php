<?php

namespace Tests\Feature\Mutations\FrontOffice\Auth;

use App\GraphQL\Mutations\FrontOffice\Auth\MemberConfirmSmsTokenMutation;
use App\Models\Auth\MemberPhoneVerification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberConfirmSmsTokenMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberConfirmSmsTokenMutation::NAME;

    public function test_confirm_sms_access_token(): void
    {
        $sms = MemberPhoneVerification::factory()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'sms' => [
                    'code' => (string)$sms->code,
                    'token' => (string)$sms->sms_token
                ],
            ],
            [
                'token',
                'expires_at'
            ]
        );

        $this->postGraphQL($query->getMutation())
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
            );
    }
}
