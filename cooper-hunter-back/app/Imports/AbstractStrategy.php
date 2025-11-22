<?php

namespace App\Imports;

abstract class AbstractStrategy implements ImportStrategyInterface
{
    abstract public function import(string $pathToFile);
}
