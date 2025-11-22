<?php

namespace Tests\Traits;

trait CarBuilder
{
    public function carBuilder()
    {
        return new \Tests\_Helpers\CarBuilder();
    }
}
