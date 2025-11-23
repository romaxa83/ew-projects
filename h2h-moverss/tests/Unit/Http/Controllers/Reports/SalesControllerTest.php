<?php

namespace Tests\Unit\Http\Controllers\Reports;

use App\Http\Controllers\Reports\SalesReportController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SalesControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected SalesReportController $controller;

    public function setUp(): void
    {
        $this->controller = resolve(SalesReportController::class);


        parent::setUp();
    }

    /** @test */
    public function check_function_rank_user_as_percent()
    {
        $data = [
            "id" => 19,
            "type" => "SalesFactCR",
            "title" => "Sales Fact, %",
            "user_1188" => "44%",
            "user_1194" => "13.68%",
            "user_1197" => "70%",
            "user_1203" => "",
            "user_1204" => "",
            "user_1205" => "10%",
            "user_1208" => "483.33%",
            "user_1214" => "10%",
            "user_1217" => "5%",
            "user_0" => "",
        ];

        $result = $this->controller->rankUsers($data);

        $this->assertEquals($result['user_0'], "");
        $this->assertEquals($result['user_1188'], 3);
        $this->assertEquals($result['user_1194'], 4);
        $this->assertEquals($result['user_1197'], 2);
        $this->assertEquals($result['user_1203'], "");
        $this->assertEquals($result['user_1204'], "");
        $this->assertEquals($result['user_1205'], 5);
        $this->assertEquals($result['user_1208'], 1);
        $this->assertEquals($result['user_1214'], 5);
        $this->assertEquals($result['user_1217'], 6);
    }

    /** @test */
    public function check_function_rank_user_as_sum()
    {
        $data = [
            "user_2337" => "$30",
            "user_2340" => "$1450",
            "user_2346" => "$30",
            "user_2349" => ""
        ];

        $result = $this->controller->rankUsers($data);

        $this->assertEquals($result['user_2337'], 2);
        $this->assertEquals($result['user_2340'], 1);
        $this->assertEquals($result['user_2346'], 2);
        $this->assertEquals($result['user_2349'], "");
    }

    private static function dataForMonth(): array
    {
        return [
            "LeadsTotal" => [
                "id" => 1,
                "type" => "LeadsTotal",
                "title" => "All incoming leads, qty",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsLost" => [
                "id" => 2,
                "type" => "LeadsLost",
                "title" => "Lost leads, qty",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsLostCR" => [
                "id" => 3,
                "type" => "LeadsLostCR",
                "title" => "Lost CR, %",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsCalculated" => [
                "id" => 4,
                "type" => "LeadsCalculated",
                "title" => "Calculation Done passed leads, qty",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsCalculatedCR" => [
                "id" => 5,
                "type" => "LeadsCalculatedCR",
                "title" => "Calculation Done passed leads CR, %",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsCalculatedSum" => [
                "id" => 6,
                "type" => "LeadsCalculatedSum",
                "title" => "Calculation Done, est. sum $",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsBooked" => [
                "id" => 7,
                "type" => "LeadsBooked",
                "title" => "Booked passed leads, qty",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsBookedCR" => [
                "id" => 8,
                "type" => "LeadsBookedCR",
                "title" => "Booked passed leads CR, %",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsBookedSum" => [
                "id" => 9,
                "type" => "LeadsBookedSum",
                "title" => "Booked leads, est. sum $",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsSuccessful" => [
                "id" => 10,
                "type" => "LeadsSuccessful",
                "title" => "Successful leads, qty",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsSuccessfulCR" => [
                "id" => 11,
                "type" => "LeadsSuccessfulCR",
                "title" => "Successful CR, %",
                "user_1" => "",
                "user_2" => "",
            ],
            "SuccessRevenue" => [
                "id" => 12,
                "type" => "SuccessRevenue",
                "title" => "Successful revenue, $",
                "user_1" => "",
                "user_2" => "",
            ],
            "SuccessAOV" => [
                "id" => 13,
                "type" => "SuccessAOV",
                "title" => "Successful AOV, $",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsDuplicate" => [
                "id" => 14,
                "type" => "LeadsDuplicate",
                "title" => "Duplicate, qty",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsBadZip" => [
                "id" => 15,
                "type" => "LeadsBadZip",
                "title" => "Bad Zip Codes, qty",
                "user_1" => "",
                "user_2" => "",
            ],
            "LeadsCantService" => [
                "id" => 16,
                "type" => "LeadsCantService",
                "title" => "Can`t service, qty",
                "user_1" => "",
                "user_2" => "",
            ],
            "SalesPlan" => [
                "id" => 17,
                "type" => "SalesPlan",
                "title" => "Sales Plan, $",
                "user_1" => "",
                "user_2" => "",
            ],
            "Left" => [
                "id" => 18,
                "type" => "Left",
                "title" => "Left, $",
                "user_1" => "",
                "user_2" => "",
            ],
            "SalesFactCR" => [
                "id" => 19,
                "type" => "SalesFactCR",
                "title" => "Sales Fact, %",
                "user_1" => "",
                "user_2" => "",
            ],
            "SalesRank" => [
                "id" => 20,
                "type" => "SalesRank",
                "title" => "Sales Rank",
                "user_1" => "",
                "user_2" => "",
            ],
            "ConversionPlan" => [
                "id" => 21,
                "type" => "ConversionPlan",
                "title" => "Conversion plan, %",
                "user_1" => "",
                "user_2" => "",
            ],
            "ConversionFact" => [
                "id" => 22,
                "type" => "ConversionFact",
                "title" => "Conversion fact, %",
                "user_1" => "",
                "user_2" => "",
            ],
            "EfficiencyRank" => [
                "id" => 23,
                "type" => "EfficiencyRank",
                "title" => "EfficiencyRank",
                "user_1" => "",
                "user_2" => "",
            ],
        ];
    }
}
