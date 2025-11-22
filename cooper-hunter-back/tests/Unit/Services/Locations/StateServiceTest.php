<?php

namespace Tests\Unit\Services\Locations;

use App\Services\Excel\Excel;
use App\Services\Locations\StateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StateServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected StateService $service;

    public function test_it_seed_states_success(): void
    {
        Excel::fake();

        $this->service->seed();

        Excel::assertImported(database_path('files/States.xlsx'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(StateService::class);
    }
}
