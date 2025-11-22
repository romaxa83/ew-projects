<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\GraphQL\Mutations\FrontOffice\Members\MemberForgotPasswordMutation;
use App\Models\Technicians\Technician;
use App\Notifications\Members\MemberForgotPasswordVerification;
use App\Services\Technicians\TechnicianVerificationService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class TechnicianForgotPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;

    public const MUTATION = MemberForgotPasswordMutation::NAME;

    private Technician|Collection $technician;
    private TechnicianVerificationService $technicianVerificationService;

    /**
     * @throws Exception
     */
    public function test_user_forgot_password(): void
    {
        Notification::fake();

        $this->query()
            ->assertOk();

        $this->assertNotificationSentTo(
            $this->technician->getEmailString(),
            MemberForgotPasswordVerification::class
        );
    }

    private function query(): TestResponse
    {
        return $this->postGraphQL(
            [
                'query' => sprintf(
                    'mutation { %s (email: "%s") }',
                    self::MUTATION,
                    $this->technician->email,
                )
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->technician = Technician::factory()->create();
        $this->technicianVerificationService = app(TechnicianVerificationService::class);
    }
}
