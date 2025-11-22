<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Members\MemberCheckPasswordMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserCheckPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberCheckPasswordMutation::NAME;

    public function test_it_check_password(): void
    {
        $this->loginAsUser();

        $query = sprintf(
            'mutation {
                %s (password: "%s")
            }',
            self::MUTATION,
            'password'
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);
    }
}
