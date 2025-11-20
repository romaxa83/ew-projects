<?php

namespace Tests\Unit\Helpers;

use App\Helpers\DateFormat;
use Carbon\Carbon;
use Tests\TestCase;

class DateFormatTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_pdf_date(): void
    {
        $date = DateFormat::pdf('2022-12-12');

        $this->assertEquals(Carbon::parse('2022-12-12')->format(DATE_ATOM), $date);
    }

    /** @test */
    public function success_pdf_empty(): void
    {
        $date = DateFormat::pdf('');

        $this->assertEquals('', $date);
    }

    /** @test */
    public function success_pdf_null(): void
    {
        $date = DateFormat::pdf(null);

        $this->assertEquals('', $date);
    }

    /** @test */
    public function success_title_null(): void
    {
        $date = DateFormat::forTitle(null);

        $this->assertEquals('', $date);
    }

    /** @test */
    public function success_title_empty(): void
    {
        $date = DateFormat::forTitle('');

        $this->assertEquals('', $date);
    }
}
