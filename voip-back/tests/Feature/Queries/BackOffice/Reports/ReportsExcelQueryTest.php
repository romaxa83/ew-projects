<?php

namespace Tests\Feature\Queries\BackOffice\Reports;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\GraphQL\Queries\BackOffice;

class ReportsExcelQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Reports\ReportsExcelQuery::NAME;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function get_url(): void
    {
        $this->loginAsSuperAdmin();

        Storage::fake('public');

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/exports/reports/reports-{$date->timestamp}.xlsx"
                    ]
                ]
            ])
        ;

        unlink(storage_path("app/public/exports/reports/reports-{$date->timestamp}.xlsx"));
    }

    /** @test */
    public function get_url_as_employee(): void
    {
        $this->loginAsEmployee();

        Storage::fake('public');

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/exports/reports/reports-{$date->timestamp}.xlsx"
                    ]
                ]
            ])
        ;

        unlink(storage_path("app/public/exports/reports/reports-{$date->timestamp}.xlsx"));
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    type
                    message
                }
            }',
            self::QUERY
        );
    }
}



