<?php

namespace App\Imports;

interface ImportStrategyInterface
{
    public function import(string $pathToFile);
}
