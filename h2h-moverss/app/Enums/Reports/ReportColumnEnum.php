<?php

namespace App\Enums\Reports;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;

/**
 * @method static LeadsTotal()
 * @method static LeadsLost()
 * @method static LeadsLostCR()
 * @method static LeadsCalculated()
 * @method static LeadsCalculatedCR()
 * @method static LeadsCalculatedSum()
 * @method static LeadsBooked()
 * @method static LeadsBookedCR()
 * @method static LeadsBookedSum()
 * @method static LeadsBookedFromCalculated()
 * @method static LeadsBookedFromCalculatedCR()
 * @method static LeadsBookedFromCalculatedSum()
 * @method static LeadsSuccessful()
 * @method static LeadsSuccessfulCR()
 * @method static SuccessRevenue()
 * @method static SuccessAOV()
 * @method static LeadsDuplicate()
 * @method static LeadsBadZip()
 * @method static LeadsCantService()
 * @method static SalesPlan()
 * @method static Left()
 * @method static SalesFactCR()
 * @method static SalesRank()
 * @method static ConversionPlan()
 * @method static ConversionFact()
 * @method static EfficiencyRank()
 * @method static LeadsQty()
 * @method static LeadsSum()
 * @method static LeadsCR()
 * @method static LeadsLostSum()
 * @method static SalesPlanQty()
 * @method static LeftQty()
 */

enum ReportColumnEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;

    case LeadsTotal = "LeadsTotal";
    case LeadsLost = "LeadsLost";
    case LeadsLostCR = "LeadsLostCR";
    case LeadsCalculated = "LeadsCalculated";
    case LeadsCalculatedCR = "LeadsCalculatedCR";
    case LeadsCalculatedSum = "LeadsCalculatedSum";
    case LeadsBooked = "LeadsBooked";
    case LeadsBookedCR = "LeadsBookedCR";
    case LeadsBookedSum = "LeadsBookedSum";
    case LeadsBookedFromCalculated = "LeadsBookedFromCalculated";
    case LeadsBookedFromCalculatedCR = "LeadsBookedFromCalculatedCR";
    case LeadsBookedFromCalculatedSum = "LeadsBookedFromCalculatedSum";
    case LeadsSuccessful = "LeadsSuccessful";
    case LeadsSuccessfulCR = "LeadsSuccessfulCR";
    case SuccessRevenue = "SuccessRevenue";
    case SuccessAOV = "SuccessAOV";
    case LeadsDuplicate = "LeadsDuplicate";
    case LeadsBadZip = "LeadsBadZip";
    case LeadsCantService = "LeadsCantService";
    case SalesPlan = "SalesPlan";
    case Left = "Left";
    case SalesFactCR = "SalesFactCR";
    case SalesRank= "SalesRank";
    case ConversionPlan = "ConversionPlan";
    case ConversionFact = "ConversionFact";
    case EfficiencyRank = "EfficiencyRank";
    case SalesPlanQty = "SalesPlanQty";
    case LeftQty = "LeftQty";

    case LeadsQty = "LeadsQty";
    case LeadsSum = "LeadsSum";
    case LeadsCR = "LeadsCR";
    case LeadsLostSum = "LeadsLostSum";

    public function label(): string
    {
        return match ($this->value){
            static::LeadsTotal->value => 'Incoming leads (qty)',

            static::LeadsLost->value => 'Lost Leads (qty)',
            static::LeadsLostCR->value => 'Lost (CR %)',

            static::LeadsCalculated->value => 'Calculation Done (qty)',
            static::LeadsCalculatedCR->value => 'Calculation Done (CR %)',
            static::LeadsCalculatedSum->value => 'Calculation Done, est. sum $',

            static::LeadsBooked->value => 'Booked Leads (qty)',
            static::LeadsBookedCR->value => 'Booked Leads (CR %)',
            static::LeadsBookedSum->value => 'Booked Leads, est. sum $',

            static::LeadsBookedFromCalculated->value => 'Booked From Calculations (qty)',
            static::LeadsBookedFromCalculatedCR->value => 'Booked From Calculations (CR %)',
            static::LeadsBookedFromCalculatedSum->value => 'Booked From Calculations, est. sum $',

            static::LeadsSuccessful->value => 'Sales Done (qty)',
            static::LeadsSuccessfulCR->value => 'Sales Done (CR %)',
            static::SuccessRevenue->value => 'Sales Done Revenue, $',
            static::SuccessAOV->value => 'Average Bill, $',

            static::LeadsDuplicate->value => 'Duplicate (qty)',
            static::LeadsBadZip->value => 'Bad Zip Codes (qty)',
            static::LeadsCantService->value => "Can`t service (qty)",

            static::SalesPlan->value => 'Sales Plan, $',
            static::SalesPlanQty->value => 'Sales Plan (qty)',
            static::Left->value => 'Left, $',
            static::LeftQty->value => 'Left (qty)',
            static::SalesFactCR->value => 'Sales Fact, %',

            static::SalesRank->value => 'Sales Rep. Rank',
            static::ConversionPlan->value => 'Conversion plan, %',
            static::ConversionFact->value => 'Conversion fact, %',
            static::EfficiencyRank->value => 'Conversion Plan Ranking',

            static::LeadsQty->value => 'Leads, qty',
            static::LeadsSum->value => 'Leads sum, $',
            static::LeadsCR->value => 'Leads, %',
            static::LeadsLostSum->value => 'Lost leads sum, $',
        };
    }


    public static function dataForSalesTable(): array
    {
        return [
            ['id' => 1, 'color' => '#2b9ddf', 'type' => self::LeadsTotal(), 'title' => self::LeadsTotal->label()],

            ['id' => 2, 'color' => '#ffbf12', 'type' => self::LeadsLost(), 'title' => self::LeadsLost->label()],
            ['id' => 3, 'color' => '#ffbf12', 'type' => self::LeadsLostCR(), 'title' => self::LeadsLostCR->label()],

            ['id' => 4, 'color' => '#2b9ddf', 'type' => self::LeadsCalculated(), 'title' => self::LeadsCalculated->label()],
            ['id' => 5, 'color' => '#2b9ddf', 'type' => self::LeadsCalculatedCR(), 'title' => self::LeadsCalculatedCR->label()],
            ['id' => 6, 'color' => '#2b9ddf', 'type' => self::LeadsCalculatedSum(), 'title' => self::LeadsCalculatedSum->label()],

            ['id' => 7, 'color' => '#ffbf12', 'type' => self::LeadsBooked(), 'title' => self::LeadsBooked->label()],
            ['id' => 8, 'color' => '#ffbf12', 'type' => self::LeadsBookedCR(), 'title' => self::LeadsBookedCR->label()],
            ['id' => 9, 'color' => '#ffbf12', 'type' => self::LeadsBookedSum(), 'title' => self::LeadsBookedSum->label()],

            ['id' => 10, 'color' => '#2b9ddf', 'type' => self::LeadsBookedFromCalculated(), 'title' => self::LeadsBookedFromCalculated->label()],
            ['id' => 11, 'color' => '#2b9ddf', 'type' => self::LeadsBookedFromCalculatedCR(), 'title' => self::LeadsBookedFromCalculatedCR->label()],
            ['id' => 12, 'color' => '#2b9ddf', 'type' => self::LeadsBookedFromCalculatedSum(), 'title' => self::LeadsBookedFromCalculatedSum->label()],

            ['id' => 13, 'color' => '#ffbf12', 'type' => self::LeadsSuccessful(), 'title' => self::LeadsSuccessful->label()],
            ['id' => 14, 'color' => '#ffbf12', 'type' => self::LeadsSuccessfulCR(), 'title' => self::LeadsSuccessfulCR->label()],
            ['id' => 15, 'color' => '#ffbf12', 'type' => self::SuccessRevenue(), 'title' => self::SuccessRevenue->label()],
            ['id' => 16, 'color' => '#ffbf12', 'type' => self::SuccessAOV(), 'title' => self::SuccessAOV->label()],

            ['id' => 17, 'color' => '#2b9ddf', 'type' => self::LeadsDuplicate(), 'title' => self::LeadsDuplicate->label()],
            ['id' => 18, 'color' => '#2b9ddf', 'type' => self::LeadsBadZip(), 'title' => self::LeadsBadZip->label()],
            ['id' => 19, 'color' => '#2b9ddf', 'type' => self::LeadsCantService(), 'title' => self::LeadsCantService->label()],

            ['id' => 20, 'color' => '#ffbf12', 'type' => self::SalesPlan(), 'title' => self::SalesPlan->label()],
            ['id' => 21, 'color' => '#ffbf12', 'type' => self::SalesPlanQty(), 'title' => self::SalesPlanQty->label()],
            ['id' => 22, 'color' => '#ffbf12', 'type' => self::Left(), 'title' => self::Left->label()],
            ['id' => 23, 'color' => '#ffbf12', 'type' => self::LeftQty(), 'title' => self::LeftQty->label()],
            ['id' => 24, 'color' => '#ffbf12', 'type' => self::SalesFactCR(), 'title' => self::SalesFactCR->label()],

            ['id' => 25, 'color' => '#2b9ddf', 'type' => self::SalesRank(), 'title' => self::SalesRank->label()],
            ['id' => 26, 'color' => '#2b9ddf', 'type' => self::ConversionPlan(), 'title' => self::ConversionPlan->label()],
            ['id' => 27, 'color' => '#2b9ddf', 'type' => self::ConversionFact(), 'title' => self::ConversionFact->label()],
            ['id' => 28, 'color' => '#2b9ddf', 'type' => self::EfficiencyRank(), 'title' => self::EfficiencyRank->label()],
        ];
    }
}

