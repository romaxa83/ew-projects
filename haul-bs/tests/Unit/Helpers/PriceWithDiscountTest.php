<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class PriceWithDiscountTest extends TestCase
{
    /** @test */
    public function get_price(): void
    {
        $price = 200;
        $discount = 10;

        $this->assertEquals(180, price_with_discount($price, $discount));
    }

    /** @test */
    public function get_price_and_discount_as_zero(): void
    {
        $price = 200;
        $discount = 0;

        $this->assertEquals(200, price_with_discount($price, $discount));
    }

    /** @test */
    public function get_price_as_zero(): void
    {
        $price = 0;
        $discount = 10;

        $this->assertEquals(0, price_with_discount($price, $discount));
    }

    /** @test */
    public function get_price_as_float(): void
    {
        $price = 25.9;
        $discount = 5.5;

        $this->assertEquals(24.48, price_with_discount($price, $discount));
    }

    /** @test */
    public function get_price_as_null(): void
    {
        $price = null;
        $discount = 10;

        $this->assertEquals(0, price_with_discount($price, $discount));
    }

    /** @test */
    public function get_price_discount_as_null(): void
    {
        $price = 10;
        $discount = null;

        $this->assertEquals(10, price_with_discount($price, $discount));
    }
}
