<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\Exceptions\Auth\TokenEncryptException;
use App\GraphQL\Mutations\FrontOffice\Members\MemberResetPasswordMutation;
use App\Models\Technicians\Technician;
use App\Services\Technicians\TechnicianVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class TechnicianResetPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;

    public const MUTATION = MemberResetPasswordMutation::NAME;

    protected TechnicianVerificationService $service;

    /**
     * @throws TokenEncryptException
     */
    public function test_reset_password_success(): void
    {
        $technician = Technician::factory()->emailNotVerified()->create();

        $password = 'Password123';

        $query = sprintf(
            'mutation { %s (token: "%s" password: "%s" password_confirmation: "%s") }',
            self::MUTATION,
            $this->service->encryptEmailToken($technician),
            $password,
            $password,
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TechnicianVerificationService::class);

        Notification::fake();
    }
}
