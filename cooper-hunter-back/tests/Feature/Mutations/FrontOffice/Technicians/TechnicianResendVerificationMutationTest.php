<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianResendEmailVerificationMutation;
use App\Models\Technicians\Technician;
use App\Notifications\Members\MemberEmailVerification;
use App\Services\Technicians\TechnicianVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class TechnicianResendVerificationMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;

    public const MUTATION = TechnicianResendEmailVerificationMutation::NAME;

    protected TechnicianVerificationService $service;

    public function test_resend_verification_success(): void
    {
        Notification::fake();

        $technician = Technician::factory()->emailNotVerified()->create();

        $this->loginAsTechnician($technician);

        $query = sprintf('mutation { %s }', self::MUTATION);
        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        $this->assertNotificationSentTo($technician->getEmailString(), MemberEmailVerification::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TechnicianVerificationService::class);
    }
}
