<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianChangePasswordMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TechnicianChangePasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = TechnicianChangePasswordMutation::NAME;

    public function test_it_change_password_success(): void
    {
        $technician = $this->loginAsTechnician();

        self::assertTrue(Hash::check('password', $technician->password));

        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            self::MUTATION,
            'password',
            'new1password',
            'new1password'
        );

        $this->postGraphQL(compact('query'))
            ->assertOk();

        $technician->refresh();

        self::assertTrue(Hash::check('new1password', $technician->password));
    }
}
