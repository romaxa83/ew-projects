<?php

namespace Tests\Traits;

use Faker\Factory;
use Faker\Generator;

trait FakerTrait
{
    public function faker(): Generator
    {
        return Factory::create();
    }
}
