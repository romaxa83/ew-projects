<?php

namespace WezomCms\Core\Tests\Unit;

use Tests\TestCase;
use WezomCms\Core\Services\PhoneMaskService;

class PhoneMaskServiceTest extends TestCase
{
    public function testEmptyPhone()
    {
        $this->assertSame('+38 (___) ___-__-__', $this->makePhoneMask()->applyMask(null));
    }

    public function testFullPhone()
    {
        $this->assertSame('+38 (050) 123-45-68', $this->makePhoneMask()->applyMask('+380501234568'));
    }

    public function testPhoneWithoutCountryCode()
    {
        $this->assertSame('+38 (050) 123-45-68', $this->makePhoneMask()->applyMask('0501234568'));
    }

    public function testPredefinedCityCodePhone()
    {
        $this->assertSame('+38 (0512) 12-34-56', $this->makePhoneMask('+38 (0512) XX-XX-XX')->applyMask('0512123456'));
    }

    public function testPartialFilling()
    {
        $this->assertSame('+38 (912) 345-6_-__', $this->makePhoneMask()->applyMask('9123456'));
    }

    public function testDigitsOverflow()
    {
        $this->assertSame('+38 (567) 898-76-54', $this->makePhoneMask()->applyMask('12345678987654'));
    }

    public function testPartialFillingWithLeadingCountryCode()
    {
        $this->assertSame('+38 (891) 234-56-__', $this->makePhoneMask()->applyMask('89123456'));
    }

    public function testMaskWithoutDigitsAndPlus()
    {
        $this->assertSame('(765) 432-16-54', $this->makePhoneMask('(XXX) XXX-XX-XX')->applyMask(987654321654));
    }

    public function testClearPhone()
    {
        $this->assertSame('+380441234568', $this->makePhoneMask()->removePhoneMask('+38 (044) 123-45-68'));
    }

    public function testClearNullPhone()
    {
        $this->assertNull($this->makePhoneMask()->removePhoneMask(null));
    }

    public function testMaskRemovalFromMaskedEmptyPhone()
    {
        $this->assertNull($this->makePhoneMask()->removePhoneMask('+38 (___) ___-__-__'));
    }

    public function testMaskRemovalOnPartialFilling()
    {
        $this->assertSame('+3806355', $this->makePhoneMask()->removePhoneMask('+38 (063) 55_-__-__'));
    }

    /**
     * @param  string|null  $format
     * @return PhoneMaskService
     */
    protected function makePhoneMask(string $format = null): PhoneMaskService
    {
        return new PhoneMaskService($format ?: '+38 (XXX) XXX-XX-XX');
    }
}
