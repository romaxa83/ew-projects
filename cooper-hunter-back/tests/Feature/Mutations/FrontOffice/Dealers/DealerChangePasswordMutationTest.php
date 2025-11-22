<?php

namespace Tests\Feature\Mutations\FrontOffice\Dealers;

use App\GraphQL\Mutations\FrontOffice\Dealers\DealerChangePasswordMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DealerChangePasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DealerChangePasswordMutation::NAME;

    /** @test */
    public function change_password_success(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        self::assertTrue(Hash::check('password', $dealer->password));

        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            self::MUTATION,
            'password',
            'new1password',
            'new1password'
        );

        $this->postGraphQL(compact('query'))
            ->assertOk();

        $dealer->refresh();

        self::assertTrue(Hash::check('new1password', $dealer->password));
    }
}
