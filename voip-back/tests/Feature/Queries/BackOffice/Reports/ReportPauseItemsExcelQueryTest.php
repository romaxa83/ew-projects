<?php

namespace Tests\Feature\Queries\BackOffice\Reports;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Reports\ReportBuilder;
use Tests\TestCase;
use App\GraphQL\Queries\BackOffice;

class ReportPauseItemsExcelQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Reports\ReportPauseItemsExcelQuery::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected ReportBuilder $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function get_url(): void
    {
        $this->loginAsSuperAdmin();

        $r = $this->reportBuilder->create();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        Storage::fake('public');

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/exports/reports/report-pause-items-{$date->timestamp}.xlsx"
                    ]
                ]
            ])
        ;

        unlink(storage_path("app/public/exports/reports/report-pause-items-{$date->timestamp}.xlsx"));
    }

    /** @test */
    public function get_url_as_employee(): void
    {
        $employee = $this->loginAsEmployee();

        $r = $this->reportBuilder->setEmployee($employee)->create();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        Storage::fake('public');

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/exports/reports/report-pause-items-{$date->timestamp}.xlsx"
                    ]
                ]
            ])
        ;

        unlink(storage_path("app/public/exports/reports/report-pause-items-{$date->timestamp}.xlsx"));
    }

    /** @test */
    public function not_perm_employee_another_report(): void
    {
        $employee = $this->loginAsEmployee();

        $r = $this->reportBuilder->setEmployee($employee)->create();
        $r_2 = $this->reportBuilder->create();

        Storage::fake('public');

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r_2->id)
        ])
        ;

        $this->assertPermission($res);
    }

    protected function getQueryStr($value): string
    {
        return sprintf(
            '
            {
                %s (report_id: %s) {
                    type
                    message
                }
            }',
            self::QUERY,
            $value
        );
    }
}
