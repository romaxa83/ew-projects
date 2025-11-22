<?php

namespace Tests\Unit\Services\Stores;

use App\Models\Stores\Distributor;
use App\Services\Stores\DistributorsImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DistributorsImportServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected DistributorsImportService $service;

    public function test_seed(): void
    {
        $this->service->seed();

        self::assertTrue(Distributor::query()->count() > 1);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(DistributorsImportService::class);
    }
}