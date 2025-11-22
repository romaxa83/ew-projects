<?php

namespace Tests\Unit\Jobs\WebSocket;

use App\Enums\SupportRequests\SupportRequestSubscriptionActionEnum;
use App\Models\Admins\Admin;
use App\Models\Support\SupportRequest;
use App\Models\Technicians\Technician;
use Core\WebSocket\Jobs\WsBroadcastJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestWebSocketJobTest extends TestCase
{
    use DatabaseTransactions;
    use SupportRequestCreateTrait;
    use AdminManagerHelperTrait;

    private Technician $technician;
    private Admin $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->technician = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $this->admin = Admin::factory()
            ->create();
    }

    public function test_job_by_create_request(): void
    {
        Bus::fake();

        $supportRequest = $this->createSupportRequest($this->technician);

        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($supportRequest): bool
            {
                return $this->checkJobData($job, null, $supportRequest);
            }
        );

        Bus::assertNotDispatched(
            function (WsBroadcastJob $job) use ($supportRequest): bool
            {
                return $this->checkJobData($job, $this->technician, $supportRequest);
            }
        );
    }

    private function checkJobData(
        WsBroadcastJob $job,
        null|Admin|Technician $user,
        SupportRequest $supportRequest,
        string $action = SupportRequestSubscriptionActionEnum::CREATED
    ): bool {
        if ($user !== null) {
            if ($job->getUser()?->id !== $user->id) {
                return false;
            }

            if ($job->getUser()
                    ?->getMorphType() !== $user::MORPH_NAME) {
                return false;
            }
        }

        $context = $job->getContext();

        if (empty($context) || !array_key_exists('support_request', $context) || !array_key_exists(
                'action',
                $context
            )) {
            return false;
        }

        if ($context['support_request'] !== $supportRequest->id || $context['action'] !== $action) {
            return false;
        }

        return true;
    }

    public function test_job_by_add_message(): void
    {
        Bus::fake();

        $supportRequest = $this->createSupportRequest($this->technician);

        $supportRequest->messages()
            ->create(
                [
                    'sender_id' => $this->admin->getId(),
                    'sender_type' => $this->admin->getMorphType(),
                    'message' => $this->faker->text,
                ]
            );

        Bus::assertDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData(
                $job,
                $this->technician,
                $supportRequest,
                SupportRequestSubscriptionActionEnum::ADDED_MESSAGE
            )
        );

        Bus::assertNotDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData(
                $job,
                $this->admin,
                $supportRequest,
                SupportRequestSubscriptionActionEnum::ADDED_MESSAGE
            )
        );
    }

    public function test_job_by_add_message_by_technician(): void
    {
        Bus::fake();

        $supportRequest = $this->createSupportRequest($this->technician);

        $supportRequest->messages()
            ->create(
                [
                    'sender_id' => $this->technician->getId(),
                    'sender_type' => $this->technician->getMorphType(),
                    'message' => $this->faker->text,
                ]
            );

        Bus::assertNotDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData(
                $job,
                $this->admin,
                $supportRequest,
                SupportRequestSubscriptionActionEnum::ADDED_MESSAGE
            )
        );

        $supportRequest->messages()
            ->create(
                [
                    'sender_id' => $this->admin->getId(),
                    'sender_type' => $this->admin->getMorphType(),
                    'message' => $this->faker->text,
                ]
            );

        $supportRequest->messages()
            ->create(
                [
                    'sender_id' => $this->technician->getId(),
                    'sender_type' => $this->technician->getMorphType(),
                    'message' => $this->faker->text,
                ]
            );

        Bus::assertDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData(
                $job,
                $this->admin,
                $supportRequest,
                SupportRequestSubscriptionActionEnum::ADDED_MESSAGE
            )
        );
    }

    public function test_job_by_closed_request(): void
    {
        Bus::fake();

        $supportRequest = $this->createSupportRequest($this->technician);
        $supportRequest->is_closed = true;
        $supportRequest->save();

        Bus::assertDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData(
                $job,
                $this->technician,
                $supportRequest,
                SupportRequestSubscriptionActionEnum::CLOSED
            )
        );
    }
}
