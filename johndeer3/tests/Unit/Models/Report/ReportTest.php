<?php

namespace Tests\Unit\Models\Report;

use App\Models\JD\Dealer;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;

class ReportTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function check_owner_user(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->create();
        $user_2 = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($user_1)
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();


        $this->assertTrue($report->isOwner($user_1));
        $this->assertFalse($report->isOwner($user_2));
    }

    /** @test */
    public function check_owner_user_dealer(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealerFR = Dealer::query()->where('country', "FR - French")->first();
        $userFR = $this->userBuilder->setDealer($dealerFR)->setRole($role)->create();

        $dealerDE = Dealer::query()->where('country', "DE - German")->first();
        $userDE = $this->userBuilder->setDealer($dealerDE)->setRole($role)->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($userFR)
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();


        $this->assertTrue($report->isOwnerDealer($userFR));
        $this->assertFalse($report->isOwnerDealer($userDE));
    }

    /** @test */
    public function check_open_edit(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $report_1 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->create();
        $report_2 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();


        $this->assertTrue($report_1->isOpenEdit());
        $this->assertFalse($report_2->isOpenEdit());
    }

    /** @test */
    public function check_process_created(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $report_1 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->create();
        $report_2 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();


        $this->assertTrue($report_2->isProcessCreated());
        $this->assertFalse($report_1->isProcessCreated());
    }

    /** @test */
    public function check_edited(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $report_1 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::EDITED)
            ->create();
        $report_2 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();


        $this->assertTrue($report_1->isEdited());
        $this->assertFalse($report_2->isEdited());
    }

    /** @test */
    public function check_verify(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $report_1 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::VERIFY)
            ->create();
        $report_2 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();


        $this->assertTrue($report_1->isVerify());
        $this->assertFalse($report_2->isVerify());
    }

    /** @test */
    public function check_created(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $report_1 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::CREATED)
            ->create();
        $report_2 = $this->reportBuilder
            ->setUser($user)
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();


        $this->assertTrue($report_1->isCreated());
        $this->assertFalse($report_2->isCreated());
    }
}
