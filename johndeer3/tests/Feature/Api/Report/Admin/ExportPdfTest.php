<?php

namespace Tests\Feature\Api\Report\Admin;

use App\Helpers\ReportHelper;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Type\ReportStatus;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Builder\UserBuilder;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Report\ReportBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExportPdfTest extends TestCase
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

        Storage::fake('public');

        $title = "some_title";
        $titlePretty = ReportHelper::titleForPdf($title);
        $path = "pdf-report/{$titlePretty}.pdf";
        $link = env('APP_URL') . "/storage/{$path}";

        $report = $this->reportBuilder->setTitle($title)->create();

        $this->getJson(route('api.report.export-pdf', [
            "report" => $report->id
        ]))
            ->assertJson($this->structureSuccessResponse([
                "link" => $link
            ]))
        ;
    }

    /** @test */
    public function fail_not_found_pdf()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->reportBuilder->create();

        $this->getJson(route('api.report.export-pdf', [
            "report" => 9999
        ]))
            ->assertJson($this->structureErrorResponse("Not found report [9999]"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $report = $this->reportBuilder->create();

        $this->getJson(route('api.report.export-pdf', [
            "report" => $report->id
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

        $this->getJson(route('api.report.export-pdf', [
            "report" => $report->id
        ]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

