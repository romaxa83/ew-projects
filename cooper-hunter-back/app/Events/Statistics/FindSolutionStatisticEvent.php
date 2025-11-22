<?php

namespace App\Events\Statistics;

use Illuminate\Support\Collection;

class FindSolutionStatisticEvent
{
    public function __construct(private Collection $solution)
    {
    }

    public function getSolution(): Collection
    {
        return $this->solution;
    }
}