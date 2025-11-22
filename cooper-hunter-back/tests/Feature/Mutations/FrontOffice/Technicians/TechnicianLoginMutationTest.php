<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\GraphQL\Mutations\FrontOffice\Members\MemberLoginMutation;
use App\Models\Technicians\Technician;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TechnicianLoginMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberLoginMutation::NAME;

    public function test_login_technician(): void
    {
        $email = new Email('technician@example.com');
        $password = 'password';

        Technician::factory()->create(compact('email'));

        $query = sprintf(
            'mutation { %s (username: "%s", password: "%s") {refresh_token access_expires_in refresh_expires_in token_type access_token } }',
            self::MUTATION,
            $email,
            $password
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'access_token',
                            'refresh_token',
                            'access_expires_in',
                            'refresh_expires_in',
                            'token_type',
                        ],
                    ],
                ]
            );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
