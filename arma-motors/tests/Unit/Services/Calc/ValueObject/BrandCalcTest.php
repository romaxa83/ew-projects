<?php

namespace Tests\Unit\Services\Calc\ValueObject;

use App\Services\Calc\Exception\BrandCalcException;
use App\Services\Calc\ValueObject\BrandCalc;
use Tests\TestCase;

class BrandCalcTest extends TestCase
{
    /** @test */
    public function create()
    {
        $obj = BrandCalc::create(BrandCalc::MITSUBISHI);

        $this->assertTrue($obj instanceof BrandCalc);
        $this->assertTrue($obj->isMitsubishi());
        $this->assertFalse($obj->isVolvo());

        $obj = BrandCalc::create(BrandCalc::RENAULT);

        $this->assertTrue($obj instanceof BrandCalc);
        $this->assertTrue($obj->isRenault());
        $this->assertFalse($obj->isVolvo());

        $obj = BrandCalc::create(BrandCalc::VOLVO);

        $this->assertTrue($obj instanceof BrandCalc);
        $this->assertTrue($obj->isVolvo());
        $this->assertFalse($obj->isRenault());
    }

    /** @test */
    public function create_wrong_brand()
    {
        $this->expectException(BrandCalcException::class);
        $this->expectExceptionMessage(__('exception.brand-calc.wrong type'));

        BrandCalc::create(44);
    }

    /** @test */
    public function check_true()
    {
        $this->assertTrue(BrandCalc::check(BrandCalc::VOLVO));
    }

    /** @test */
    public function check_false()
    {
        $this->assertFalse(BrandCalc::check(44));
    }
}
