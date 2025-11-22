<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\Exceptions\Auth\TokenEncryptException;
use App\GraphQL\Mutations\FrontOffice\Members\MemberEmailConfirmationMutation;
use App\Models\Technicians\Technician;
use App\Services\Technicians\TechnicianVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TechnicianEmailVerificationMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberEmailConfirmationMutation::NAME;

    protected TechnicianVerificationService $service;

    /** @throws TokenEncryptException */
    public function test_it_verify_email_success(): void
    {
        $technician = Technician::factory()->emailNotVerified()->create();

        $token = $this->service->encryptEmailToken($technician);

        $query = sprintf(
            'mutation { %s ( token: "%s" )}',
            self::MUTATION,
            $token
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();

        $this->service = app(TechnicianVerificationService::class);
    }
}
