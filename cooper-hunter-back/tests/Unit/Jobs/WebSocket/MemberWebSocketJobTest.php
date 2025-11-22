<?php

namespace Tests\Unit\Jobs\WebSocket;

use App\Contracts\Members\Member;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Core\WebSocket\Jobs\WsBroadcastJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class MemberWebSocketJobTest extends TestCase
{
    use DatabaseTransactions;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    private Technician $technician;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->technician = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $this->user = User::factory()
            ->create();
    }

    public function test_job_by_update_technician(): void
    {
        Bus::fake();

        $this->technician->is_verified = false;
        $this->technician->save();

        Bus::assertDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData($job, $this->technician)
        );
    }

    private function checkJobData(
        WsBroadcastJob $job,
        Member $user
    ): bool
    {
        if (!$job->getUser()) {
            return false;
        }

        if ($job->getUser()->id !== $user->getId()) {
            return false;
        }

        if ($job->getUser()
                ->getMorphType() !== $user->getMorphType()) {
            return false;
        }

        $context = $job->getContext();

        if (empty($context) || !array_key_exists('member', $context) || !array_key_exists('type', $context)) {
            return false;
        }

        if ($context['member'] !== $user->getId() || $context['type'] !== $user->getMorphType()) {
            return false;
        }

        return true;
    }

    public function test_job_by_update_user(): void
    {
        Bus::fake();

        $this->user->email_verified_at = null;
        $this->user->save();

        Bus::assertDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData($job, $this->user)
        );
    }
}
