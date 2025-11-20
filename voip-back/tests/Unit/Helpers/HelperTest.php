<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class HelperTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function convert_millisecond_to_second(): void
    {
        $this->assertEquals(1, convertMillisecondToSecond(1000));
        $this->assertEquals(1, convertMillisecondToSecond(1050));
        $this->assertEquals(2, convertMillisecondToSecond(2000));
        $this->assertEquals(2, convertMillisecondToSecond(2600));
    }

    /** @test */
    public function value_for_excel_row(): void
    {
        $this->assertEquals('-', valueForExcelRow(null));
        $this->assertEquals('-', valueForExcelRow(0));
        $this->assertEquals('-', valueForExcelRow('0'));
        $this->assertEquals('-', valueForExcelRow(''));
        $this->assertEquals('-', valueForExcelRow(' '));
        $this->assertEquals('-', valueForExcelRow('00:00:00'));
        $this->assertEquals('-', valueForExcelRow(\App\Models\Reports\Item::UNKNOWN));
        $this->assertEquals('str', valueForExcelRow('str'));
        $this->assertEquals('11', valueForExcelRow(11));
    }

    /** @test */
    public function second_to_time(): void
    {
        $this->assertEquals('00:00:00', secondToTime(0));
        $this->assertEquals('00:00:01', secondToTime(1));
        $this->assertEquals('00:00:50', secondToTime(50));
        $this->assertEquals('00:08:20', secondToTime(500));
        $this->assertEquals('01:23:20', secondToTime(5000));
        $this->assertEquals('47:41:11', secondToTime(171671));
        $this->assertEquals('1087:59:00', secondToTime(3916740));
    }
}


