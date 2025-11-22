<?php

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\OrderCreditDTO;
use App\DTO\Order\OrderInsuranceDTO;
use App\DTO\Order\OrderPdfDTO;
use App\Models\Order\Order;
use App\Services\AA\Commands\GetAct;
use App\Services\AA\Commands\GetInvoice;
use App\Types\Communication;
use App\Types\UserType;
use App\ValueObjects\Money;
use Illuminate\Support\Arr;
use Tests\TestCase;

class OrderPdfDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_bill()
    {
        $data = GetInvoice::testData();
        $dto = resolve(OrderPdfDTO::class)->fill($data, Order::FILE_BILL_TYPE);

        $this->assertNull(Arr::get($dto, 'title'));
        $this->assertEquals(Arr::get($dto, 'contactInformation'), Arr::get($data, 'contactInformation'));
        $this->assertEquals(Arr::get($dto, 'date'), Arr::get($data, 'date'));
        $this->assertEquals(Arr::get($dto, 'organization'), Arr::get($data, 'organization'));
        $this->assertEquals(Arr::get($dto, 'number'), Arr::get($data, 'number'));
        $this->assertEquals(Arr::get($dto, 'shopper'), Arr::get($data, 'shopper'));
        $this->assertEquals(Arr::get($dto, 'address'), Arr::get($data, 'address'));
        $this->assertEquals(Arr::get($dto, 'phone'), Arr::get($data, 'phone'));
        $this->assertEquals(Arr::get($dto, 'etc'), Arr::get($data, 'etc'));
        $this->assertEquals(Arr::get($dto, 'taxCode'), Arr::get($data, 'taxCode'));
        $this->assertEquals(Arr::get($dto, 'discount'), Arr::get($data, 'discount'));
        $this->assertEquals(Arr::get($dto, 'amountWithoutVAT'), Arr::get($data, 'amountWithoutVAT'));
        $this->assertEquals(Arr::get($dto, 'amountVAT'), Arr::get($data, 'amountVAT'));
        $this->assertEquals(Arr::get($dto, 'amountIncludingVAT'), Arr::get($data, 'amountIncludingVAT'));
        $this->assertEquals(Arr::get($dto, 'author'), Arr::get($data, 'author'));

        foreach (Arr::get($data, 'parts') as $key => $item){
            $this->assertEquals(Arr::get($dto, "parts.{$key}.sum"), Arr::get($item, 'sum'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.ref"), Arr::get($item, 'ref'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.discountedPrice"), Arr::get($item, 'discountedPrice'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.name"), Arr::get($item, 'name'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.price"), Arr::get($item, 'price'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.quantity"), Arr::get($item, 'quantity'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.unit"), Arr::get($item, 'unit'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.rate"), Arr::get($item, 'rate'));
        }
    }

    /** @test */
    public function check_fill_by_act()
    {
        $data = GetAct::testData();
        $dto = resolve(OrderPdfDTO::class)->fill($data, Order::FILE_ACT_TYPE);

        $this->assertNull(Arr::get($dto, 'title'));
        $this->assertEquals(Arr::get($dto, 'jobsAmountVAT'), Arr::get($data, 'jobsAmountVAT'));
        $this->assertEquals(Arr::get($dto, 'payer.name'), Arr::get($data, 'payer.name'));
        $this->assertEquals(Arr::get($dto, 'payer.date'), Arr::get($data, 'payer.date'));
        $this->assertEquals(Arr::get($dto, 'payer.contract'), Arr::get($data, 'payer.contract'));
        $this->assertEquals(Arr::get($dto, 'payer.number'), Arr::get($data, 'payer.number'));
        $this->assertEquals(Arr::get($dto, 'repairType'), Arr::get($data, 'repairType'));
        $this->assertEquals(Arr::get($dto, 'number'), Arr::get($data, 'number'));
        $this->assertEquals(Arr::get($dto, 'closingDate'), Arr::get($data, 'closingDate'));
        $this->assertEquals(Arr::get($dto, 'organization.name'), Arr::get($data, 'organization.name'));
        $this->assertEquals(Arr::get($dto, 'organization.phone'), Arr::get($data, 'organization.phone'));
        $this->assertEquals(Arr::get($dto, 'organization.address'), Arr::get($data, 'organization.address'));
        $this->assertEquals(Arr::get($dto, 'dealer'), Arr::get($data, 'dealer'));

        foreach (Arr::get($data, 'jobs') as $key => $item){
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.name"), Arr::get($item, 'name'));
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.ref"), Arr::get($item, 'ref'));
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.coefficient"), Arr::get($item, 'coefficient'));
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.priceWithVAT"), Arr::get($item, 'priceWithVAT'));
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.priceWithoutVAT"), Arr::get($item, 'priceWithoutVAT'));
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.amountWithoutVAT"), Arr::get($item, 'amountWithoutVAT'));
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.price"), Arr::get($item, 'price'));
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.amountIncludingVAT"), Arr::get($item, 'amountIncludingVAT'));
            $this->assertEquals(Arr::get($dto, "jobs.{$key}.rate"), Arr::get($item, 'rate'));
        }

        $this->assertEquals(Arr::get($dto, 'AmountInWords'), Arr::get($data, 'AmountInWords'));
        $this->assertEquals(Arr::get($dto, 'date'), Arr::get($data, 'date'));
        $this->assertEquals(Arr::get($dto, 'mileage'), Arr::get($data, 'mileage'));
        $this->assertEquals(Arr::get($dto, 'currentAccount'), Arr::get($data, 'currentAccount'));

        $this->assertEquals(Arr::get($dto, 'owner.name'), Arr::get($data, 'owner.name'));
        $this->assertEquals(Arr::get($dto, 'owner.phone'), Arr::get($data, 'owner.phone'));
        $this->assertEquals(Arr::get($dto, 'owner.address'), Arr::get($data, 'owner.address'));
        $this->assertEquals(Arr::get($dto, 'owner.email'), Arr::get($data, 'owner.email'));
        $this->assertEquals(Arr::get($dto, 'owner.etc'), Arr::get($data, 'owner.etc'));
        $this->assertEquals(Arr::get($dto, 'owner.certificate'), Arr::get($data, 'owner.certificate'));

        $this->assertEquals(Arr::get($dto, 'partsAmountIncludingVAT'), Arr::get($data, 'partsAmountIncludingVAT'));

        $this->assertEquals(Arr::get($dto, 'customer.name'), Arr::get($data, 'customer.name'));
        $this->assertEquals(Arr::get($dto, 'customer.FIO'), Arr::get($data, 'customer.FIO'));
        $this->assertEquals(Arr::get($dto, 'customer.phone'), Arr::get($data, 'customer.phone'));
        $this->assertEquals(Arr::get($dto, 'customer.email'), Arr::get($data, 'customer.email'));
        $this->assertEquals(Arr::get($dto, 'customer.date'), Arr::get($data, 'customer.date'));
        $this->assertEquals(Arr::get($dto, 'customer.number'), Arr::get($data, 'customer.number'));

        $this->assertEquals(Arr::get($dto, 'model'), Arr::get($data, 'model'));
        $this->assertEquals(Arr::get($dto, 'bodyNumber'), Arr::get($data, 'bodyNumber'));
        $this->assertEquals(Arr::get($dto, 'dateOfSale'), Arr::get($data, 'dateOfSale'));
        $this->assertEquals(Arr::get($dto, 'stateNumber'), Arr::get($data, 'stateNumber'));
        $this->assertEquals(Arr::get($dto, 'producer'), Arr::get($data, 'producer'));

        $this->assertEquals(Arr::get($dto, 'dispatcher.position'), Arr::get($data, 'dispatcher.position'));
        $this->assertEquals(Arr::get($dto, 'dispatcher.name'), Arr::get($data, 'dispatcher.name'));
        $this->assertEquals(Arr::get($dto, 'dispatcher.date'), Arr::get($data, 'dispatcher.date'));
        $this->assertEquals(Arr::get($dto, 'dispatcher.number'), Arr::get($data, 'dispatcher.number'));
        $this->assertEquals(Arr::get($dto, 'dispatcher.FIO'), Arr::get($data, 'dispatcher.FIO'));

        foreach (Arr::get($data, 'parts') as $key => $item){
            $this->assertEquals(Arr::get($dto, "parts.{$key}.unit"), Arr::get($item, 'unit'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.producer"), Arr::get($item, 'producer'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.ref"), Arr::get($item, 'ref'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.name"), Arr::get($item, 'name'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.price"), Arr::get($item, 'price'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.quantity"), Arr::get($item, 'quantity'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.priceWithVAT"), Arr::get($item, 'priceWithVAT'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.priceWithoutVAT"), Arr::get($item, 'priceWithoutVAT'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.rate"), Arr::get($item, 'rate'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.amountWithoutVAT"), Arr::get($item, 'amountWithoutVAT'));
            $this->assertEquals(Arr::get($dto, "parts.{$key}.amountIncludingVAT"), Arr::get($item, 'amountIncludingVAT'));
        }

        $this->assertEquals(Arr::get($dto, 'disassembledParts'), Arr::get($data, 'disassembledParts'));
        $this->assertEquals(Arr::get($dto, 'AmountIncludingVAT'), Arr::get($data, 'AmountIncludingVAT'));
        $this->assertEquals(Arr::get($dto, 'recommendations'), Arr::get($data, 'recommendations'));
        $this->assertEquals(Arr::get($dto, 'AmountVAT'), Arr::get($data, 'AmountVAT'));
        $this->assertEquals(Arr::get($dto, 'discountParts'), Arr::get($data, 'discountParts'));
        $this->assertEquals(Arr::get($dto, 'discountJobs'), Arr::get($data, 'discountJobs'));
        $this->assertEquals(Arr::get($dto, 'discount'), Arr::get($data, 'discount'));
        $this->assertEquals(Arr::get($dto, 'jobsAmountWithoutVAT'), Arr::get($data, 'jobsAmountWithoutVAT'));
        $this->assertEquals(Arr::get($dto, 'jobsAmountIncludingVAT'), Arr::get($data, 'jobsAmountIncludingVAT'));
        $this->assertEquals(Arr::get($dto, 'partsAmountWithoutVAT'), Arr::get($data, 'partsAmountWithoutVAT'));
        $this->assertEquals(Arr::get($dto, 'partsAmountVAT'), Arr::get($data, 'partsAmountVAT'));
        $this->assertEquals(Arr::get($dto, 'AmountWithoutVAT'), Arr::get($data, 'AmountWithoutVAT'));
    }

    /** @test */
    public function check_emptyl()
    {
        $dto = resolve(OrderPdfDTO::class)->fill([], "wrong");

        $this->assertEmpty($dto);
    }
}



