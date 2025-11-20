<?php

namespace Tests\Feature\Api\Report\Admin;

use App\Helpers\ReportHelper;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Notifications\SendReport;
use App\Type\ReportStatus;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Builder\UserBuilder;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Report\ReportBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VerifyTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Notification::fake();
        Storage::fake('public');

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setComment('comment')
            ->setStatus(ReportStatus::CREATED)
            ->setTitle("some_title")
            ->create();

        $this->assertFalse($report->isVerify());
        $this->assertNotNull($report->comment);

        $this->getJson(route('admin.report.verify', [
            "report" => $report
        ]))
            ->assertJson($this->structureSuccessResponse([
                "id" => $report->id,
                "status" => ReportStatus::VERIFY,
                "verify" => true,
                "comment" => null
            ]))
        ;

        $report->refresh();

        $this->assertTrue($report->isVerify());
        $this->assertNull($report->comment);

        $title = ReportHelper::titleForPdf($report->title);
        Storage::disk('public')
            ->assertExists("pdf-report/{$title}.pdf");

        Notification::assertSentTo(new AnonymousNotifiable(), SendReport::class,
            function ($notification, $channels, $notifiable) use ($report) {
                return $notifiable->routes['mail'] == $report->client_email;
            }
        );
    }

    /** @test */
    public function fail_cannot_toggle_status()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $report Report */
        $report_1 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)->create();
        $report_2 = $this->reportBuilder->setStatus(ReportStatus::VERIFY)->create();
        $report_3 = $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)->create();

        $this->getJson(route('admin.report.verify', [
            "report" => $report_1
        ]))
            ->assertJson($this->structureErrorResponse(__('message.cannot toggle report to verify status')))
        ;

        $this->getJson(route('admin.report.verify', [
            "report" => $report_2
        ]))
            ->assertJson($this->structureErrorResponse(__('message.cannot toggle report to verify status')))
        ;

        $this->getJson(route('admin.report.verify', [
            "report" => $report_3
        ]))
            ->assertJson($this->structureErrorResponse(__('message.cannot toggle report to verify status')))
        ;
    }

    /** @test */
    public function fail_have_not_client_email()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $report = $this->reportBuilder->setClientEmail(null)
            ->setStatus(ReportStatus::CREATED)->create();

        $this->assertNull($report->client_email);

        $this->getJson(route('admin.report.verify', [
            "report" => $report
        ]))
            ->assertJson($this->structureErrorResponse(
                __('message.exceptions.report don\'t have a client email')
            ))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setStatus(ReportStatus::CREATED)
            ->create();

        $this->getJson(route('admin.report.verify', [
            "report" => $report
        ]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->userBuilder->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setStatus(ReportStatus::CREATED)
            ->create();

        $this->getJson(route('admin.report.verify', [
            "report" => $report
        ]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

