<?php

namespace App\Imports\Spares\Strategies;

abstract class AbstractStrategy implements SparesImportStrategyInterface
{
    abstract public function import(string $pathToFile);
}
