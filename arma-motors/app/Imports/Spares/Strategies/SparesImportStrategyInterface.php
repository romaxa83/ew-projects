<?php

namespace App\Imports\Spares\Strategies;

interface SparesImportStrategyInterface
{
    public function import(string $pathToFile);
}
