<?php

namespace Tests\Unit\Service\Export;

use App\Services\Export\ExcelService;
use Tests\TestCase;

class ExcelServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_create_country(): void
    {
        $data = [];

        $service = app(ExcelService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Call to a member function all() on array");

        $service->generateAndSave($data);
    }
}


