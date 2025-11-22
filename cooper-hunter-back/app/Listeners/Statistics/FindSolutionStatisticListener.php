<?php

namespace App\Listeners\Statistics;

use App\Events\Statistics\FindSolutionStatisticEvent;
use App\Services\Catalog\Solutions\SolutionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use JsonException;

class FindSolutionStatisticListener implements ShouldQueue
{
    public function __construct(private SolutionService $service)
    {
    }

    /**
     * @throws JsonException
     */
    public function handle(FindSolutionStatisticEvent $event): void
    {
        $this->service->storeStatistic($event->getSolution());
    }
}