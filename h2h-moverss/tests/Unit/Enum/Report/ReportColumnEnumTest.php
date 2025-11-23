<?php

namespace Tests\Unit\Enum\Report;

use App\Enums\Reports\ReportColumnEnum;
use Tests\TestCase;

class ReportColumnEnumTest extends TestCase
{
    /** @test */
    public function check_value()
    {
        $this->assertEquals('LeadsTotal', ReportColumnEnum::LeadsTotal());

        $this->assertEquals('LeadsLost', ReportColumnEnum::LeadsLost());
        $this->assertEquals('LeadsLostCR', ReportColumnEnum::LeadsLostCR());

        $this->assertEquals('LeadsCalculated', ReportColumnEnum::LeadsCalculated());
        $this->assertEquals('LeadsCalculatedCR', ReportColumnEnum::LeadsCalculatedCR());
        $this->assertEquals('LeadsCalculatedSum', ReportColumnEnum::LeadsCalculatedSum());

        $this->assertEquals('LeadsBooked', ReportColumnEnum::LeadsBooked());
        $this->assertEquals('LeadsBookedCR', ReportColumnEnum::LeadsBookedCR());
        $this->assertEquals('LeadsBookedSum', ReportColumnEnum::LeadsBookedSum());

        $this->assertEquals('LeadsBookedFromCalculated', ReportColumnEnum::LeadsBookedFromCalculated());
        $this->assertEquals('LeadsBookedFromCalculatedCR', ReportColumnEnum::LeadsBookedFromCalculatedCR());
        $this->assertEquals('LeadsBookedFromCalculatedSum', ReportColumnEnum::LeadsBookedFromCalculatedSum());

        $this->assertEquals('LeadsSuccessful', ReportColumnEnum::LeadsSuccessful());
        $this->assertEquals('LeadsSuccessfulCR', ReportColumnEnum::LeadsSuccessfulCR());
        $this->assertEquals('SuccessRevenue', ReportColumnEnum::SuccessRevenue());
        $this->assertEquals('SuccessAOV', ReportColumnEnum::SuccessAOV());

        $this->assertEquals('LeadsDuplicate', ReportColumnEnum::LeadsDuplicate());
        $this->assertEquals('LeadsBadZip', ReportColumnEnum::LeadsBadZip());
        $this->assertEquals('LeadsCantService', ReportColumnEnum::LeadsCantService());

        $this->assertEquals('SalesPlan', ReportColumnEnum::SalesPlan());
        $this->assertEquals('SalesPlanQty', ReportColumnEnum::SalesPlanQty());
        $this->assertEquals('Left', ReportColumnEnum::Left());
        $this->assertEquals('LeftQty', ReportColumnEnum::LeftQty());
        $this->assertEquals('SalesFactCR', ReportColumnEnum::SalesFactCR());

        $this->assertEquals('SalesRank', ReportColumnEnum::SalesRank());
        $this->assertEquals('ConversionPlan', ReportColumnEnum::ConversionPlan());
        $this->assertEquals('ConversionFact', ReportColumnEnum::ConversionFact());
        $this->assertEquals('EfficiencyRank', ReportColumnEnum::EfficiencyRank());

        $this->assertEquals('LeadsQty', ReportColumnEnum::LeadsQty());
        $this->assertEquals('LeadsSum', ReportColumnEnum::LeadsSum());
        $this->assertEquals('LeadsCR', ReportColumnEnum::LeadsCR());
        $this->assertEquals('LeadsLostSum', ReportColumnEnum::LeadsLostSum());
    }

    /** @test */
    public function check_label()
    {
        $this->assertEquals('Incoming leads (qty)', ReportColumnEnum::LeadsTotal->label());

        $this->assertEquals('Lost Leads (qty)', ReportColumnEnum::LeadsLost->label());
        $this->assertEquals('Lost (CR %)', ReportColumnEnum::LeadsLostCR->label());

        $this->assertEquals('Calculation Done (qty)', ReportColumnEnum::LeadsCalculated->label());
        $this->assertEquals('Calculation Done (CR %)', ReportColumnEnum::LeadsCalculatedCR->label());
        $this->assertEquals('Calculation Done, est. sum $', ReportColumnEnum::LeadsCalculatedSum->label());

        $this->assertEquals('Booked Leads (qty)', ReportColumnEnum::LeadsBooked->label());
        $this->assertEquals('Booked Leads (CR %)', ReportColumnEnum::LeadsBookedCR->label());
        $this->assertEquals('Booked Leads, est. sum $', ReportColumnEnum::LeadsBookedSum->label());

        $this->assertEquals('Booked From Calculations (qty)', ReportColumnEnum::LeadsBookedFromCalculated->label());
        $this->assertEquals('Booked From Calculations (CR %)', ReportColumnEnum::LeadsBookedFromCalculatedCR->label());
        $this->assertEquals('Booked From Calculations, est. sum $', ReportColumnEnum::LeadsBookedFromCalculatedSum->label());

        $this->assertEquals('Sales Done (qty)', ReportColumnEnum::LeadsSuccessful->label());
        $this->assertEquals('Sales Done (CR %)', ReportColumnEnum::LeadsSuccessfulCR->label());
        $this->assertEquals('Sales Done Revenue, $', ReportColumnEnum::SuccessRevenue->label());
        $this->assertEquals('Average Bill, $', ReportColumnEnum::SuccessAOV->label());

        $this->assertEquals('Duplicate (qty)', ReportColumnEnum::LeadsDuplicate->label());
        $this->assertEquals('Bad Zip Codes (qty)', ReportColumnEnum::LeadsBadZip->label());
        $this->assertEquals('Can`t service (qty)', ReportColumnEnum::LeadsCantService->label());

        $this->assertEquals('Sales Plan, $', ReportColumnEnum::SalesPlan->label());
        $this->assertEquals('Sales Plan (qty)', ReportColumnEnum::SalesPlanQty->label());
        $this->assertEquals('Left, $', ReportColumnEnum::Left->label());
        $this->assertEquals('Left (qty)', ReportColumnEnum::LeftQty->label());
        $this->assertEquals('Sales Fact, %', ReportColumnEnum::SalesFactCR->label());

        $this->assertEquals('Sales Rep. Rank', ReportColumnEnum::SalesRank->label());
        $this->assertEquals('Conversion plan, %', ReportColumnEnum::ConversionPlan->label());
        $this->assertEquals('Conversion fact, %', ReportColumnEnum::ConversionFact->label());
        $this->assertEquals('Conversion Plan Ranking', ReportColumnEnum::EfficiencyRank->label());

        $this->assertEquals('Leads, qty', ReportColumnEnum::LeadsQty->label());
        $this->assertEquals('Leads sum, $', ReportColumnEnum::LeadsSum->label());
        $this->assertEquals('Leads, %', ReportColumnEnum::LeadsCR->label());
        $this->assertEquals('Lost leads sum, $', ReportColumnEnum::LeadsLostSum->label());
    }

    /** @test */
    public function check_data_for_sales_table()
    {
        $this->assertEquals(ReportColumnEnum::dataForSalesTable(), [
            ['id' => 1, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsTotal(), 'title' => ReportColumnEnum::LeadsTotal->label()],
            ['id' => 2, 'color' => '#ffbf12', 'type' => ReportColumnEnum::LeadsLost(), 'title' => ReportColumnEnum::LeadsLost->label()],
            ['id' => 3, 'color' => '#ffbf12', 'type' => ReportColumnEnum::LeadsLostCR(), 'title' => ReportColumnEnum::LeadsLostCR->label()],
            ['id' => 4, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsCalculated(), 'title' => ReportColumnEnum::LeadsCalculated->label()],
            ['id' => 5, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsCalculatedCR(), 'title' => ReportColumnEnum::LeadsCalculatedCR->label()],
            ['id' => 6, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsCalculatedSum(), 'title' => ReportColumnEnum::LeadsCalculatedSum->label()],
            ['id' => 7, 'color' => '#ffbf12', 'type' => ReportColumnEnum::LeadsBooked(), 'title' => ReportColumnEnum::LeadsBooked->label()],
            ['id' => 8, 'color' => '#ffbf12', 'type' => ReportColumnEnum::LeadsBookedCR(), 'title' => ReportColumnEnum::LeadsBookedCR->label()],
            ['id' => 9, 'color' => '#ffbf12', 'type' => ReportColumnEnum::LeadsBookedSum(), 'title' => ReportColumnEnum::LeadsBookedSum->label()],
            ['id' => 10, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsBookedFromCalculated(), 'title' => ReportColumnEnum::LeadsBookedFromCalculated->label()],
            ['id' => 11, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsBookedFromCalculatedCR(), 'title' => ReportColumnEnum::LeadsBookedFromCalculatedCR->label()],
            ['id' => 12, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsBookedFromCalculatedSum(), 'title' => ReportColumnEnum::LeadsBookedFromCalculatedSum->label()],
            ['id' => 13, 'color' => '#ffbf12', 'type' => ReportColumnEnum::LeadsSuccessful(), 'title' => ReportColumnEnum::LeadsSuccessful->label()],
            ['id' => 14, 'color' => '#ffbf12', 'type' => ReportColumnEnum::LeadsSuccessfulCR(), 'title' => ReportColumnEnum::LeadsSuccessfulCR->label()],
            ['id' => 15, 'color' => '#ffbf12', 'type' => ReportColumnEnum::SuccessRevenue(), 'title' => ReportColumnEnum::SuccessRevenue->label()],
            ['id' => 16, 'color' => '#ffbf12', 'type' => ReportColumnEnum::SuccessAOV(), 'title' => ReportColumnEnum::SuccessAOV->label()],
            ['id' => 17, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsDuplicate(), 'title' => ReportColumnEnum::LeadsDuplicate->label()],
            ['id' => 18, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsBadZip(), 'title' => ReportColumnEnum::LeadsBadZip->label()],
            ['id' => 19, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::LeadsCantService(), 'title' => ReportColumnEnum::LeadsCantService->label()],
            ['id' => 20, 'color' => '#ffbf12', 'type' => ReportColumnEnum::SalesPlan(), 'title' => ReportColumnEnum::SalesPlan->label()],
            ['id' => 21, 'color' => '#ffbf12', 'type' => ReportColumnEnum::SalesPlanQty(), 'title' => ReportColumnEnum::SalesPlanQty->label()],
            ['id' => 22, 'color' => '#ffbf12', 'type' => ReportColumnEnum::Left(), 'title' => ReportColumnEnum::Left->label()],
            ['id' => 23, 'color' => '#ffbf12', 'type' => ReportColumnEnum::LeftQty(), 'title' => ReportColumnEnum::LeftQty->label()],
            ['id' => 24, 'color' => '#ffbf12', 'type' => ReportColumnEnum::SalesFactCR(), 'title' => ReportColumnEnum::SalesFactCR->label()],
            ['id' => 25, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::SalesRank(), 'title' => ReportColumnEnum::SalesRank->label()],
            ['id' => 26, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::ConversionPlan(), 'title' => ReportColumnEnum::ConversionPlan->label()],
            ['id' => 27, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::ConversionFact(), 'title' => ReportColumnEnum::ConversionFact->label()],
            ['id' => 28, 'color' => '#2b9ddf', 'type' => ReportColumnEnum::EfficiencyRank(), 'title' => ReportColumnEnum::EfficiencyRank->label()],
        ]);
    }
}
