<?php

namespace Tests\Unit\Helpers;

use App\Helpers\DateTime;
use Carbon\Carbon;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    /** @test */
    public function from_seconds_to_milliseconds_success_as_int()
    {
        $second = 3500;
        $expect = 3500000;
        $millisecond = DateTime::fromSecondsToMilliseconds($second);

        $this->assertEquals($expect, $millisecond);
    }

    /** @test */
    public function from_seconds_to_milliseconds_success_as_str()
    {
        $second = "3500";
        $expect = 3500000;
        $millisecond = DateTime::fromSecondsToMilliseconds($second);

        $this->assertEquals($expect, $millisecond);
    }

    /** @test */
    public function from_seconds_to_milliseconds_fail()
    {
        $second = "ddd";

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('incorrect data for convert date', ['data' => $second]));

        DateTime::fromSecondsToMilliseconds($second);
    }

    /** @test */
    public function from_milliseconds_to_second_success_as_int()
    {
        $millisecond = 3500000;
        $expect = 3500;
        $second = DateTime::fromMillisecondToSeconds($millisecond);

        $this->assertEquals($expect, $second);
    }

    /** @test */
    public function from_milliseconds_to_second_success_as_str()
    {
        $millisecond = "3500000";
        $expect = 3500;
        $second = DateTime::fromMillisecondToSeconds($millisecond);

        $this->assertEquals($expect, $second);
    }

    /** @test */
    public function from_milliseconds_to_second_success_as_zero()
    {
        $millisecond = 0;
        $expect = 0;
        $second = DateTime::fromMillisecondToSeconds($millisecond);

        $this->assertEquals($expect, $second);
    }

    /** @test */
    public function from_milliseconds_to_second_fail()
    {
        $second = "ddd";

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('incorrect data for convert date', ['data' => $second]));

        DateTime::fromMillisecondToSeconds($second);
    }

    /** @test */
    public function from_milliseconds_to_date()
    {
        $millisecond = "1630875600000";
        $expect = "2021-09-06 00:00:00";

        $date = DateTime::fromMillisecondToDate($millisecond);

        $this->assertEquals($expect, $date);
    }

    /** @test */
    public function from_milliseconds_to_date_fail()
    {
        $millisecond = "dd";

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('incorrect data for convert date', ['data' => $millisecond]));

        DateTime::fromMillisecondToDate($millisecond);
    }

    /** @test */
    public function from_date_to_milliseconds_fail()
    {
        $data = Carbon::now();
        $expect = DateTime::fromSecondsToMilliseconds($data->timestamp);

        $date = DateTime::fromDateToMillisecond($data);

        $this->assertEquals($expect, $date);
    }

    /** @test */
    public function get_day_of_week_from_millisecond()
    {
        $millisecond = Carbon::now()->timestamp * 1000;
        $expect = Carbon::now()->dayOfWeek;

        $day = DateTime::fromMillisecondToDayOfWeek($millisecond);

        $this->assertEquals($expect, $day);
    }

}



