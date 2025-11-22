<?php

namespace Tests\Unit\Services\Projects\Systems;

use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Projects\System;
use App\Services\Projects\SystemService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
use Tests\TestCase;

class SystemServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var SystemService
     */
    protected mixed $service;
    protected string $exceptionMessage;

    /**
     * Creating new system with same used serial
     */
    public function test_case_1(): void
    {
        [$serial] = $this->createData();

        $this->execute([$serial]);

        $this->assertTrue(true);
    }

    protected function createData(): array
    {
        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()
                    ->times(10),
                'serialNumbers'
            )
            ->create();

        $unusedSerials = $product->serialNumbers;

        $usedSerial = $unusedSerials->shift()->serial_number;

        System::factory()
            ->hasAttached(
                $product,
                fn($a) => ['serial_number' => $usedSerial],
                relationship: 'units'
            )
            ->create();

        return [
            $usedSerial,
            $unusedSerials
        ];
    }

    protected function execute(array $serialNumbers): void
    {
        $service = $this->service;

        /** @see SystemService::assertUnitsAreCompleted() */
        $method = new ReflectionMethod($service, 'assertUnitsAreCompleted');
        $method->setAccessible(true);

        $method->invoke($service, $serialNumbers);
    }

    /**
     * Creating new system with one used serial and one never used
     */
    public function test_case_2(): void
    {
        $this->expectExceptionMessage($this->exceptionMessage);

        [$serial, $serials] = $this->createData();

        $this->execute([$serial, $serials->shift()->serial_number]);
    }

    /**
     * Creating new system with never used serial
     */
    public function test_case_3(): void
    {
        [, $serials] = $this->createData();

        $this->execute([$serials->shift()->serial_number]);

        $this->assertTrue(true);
    }

    /**
     * Creating the duplicate of system
     */
    public function test_case_4(): void
    {
        [$serial] = $this->createData();

        System::factory()
            ->hasAttached(
                Product::factory(),
                fn($a) => ['serial_number' => $serial],
                relationship: 'units'
            )
            ->create();

        $this->execute([$serial]);

        $this->assertTrue(true);
    }

    public function test_case_5(): void
    {
        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()
                    ->times(3),
                'serialNumbers'
            )
            ->create();

        $serials = $product->serialNumbers()->get();

        //System with 3 used serials
        $s = System::factory()
            ->hasAttached(
                Product::factory()->times(3),
                fn($a) => ['serial_number' => $serials->shift()->serial_number],
                relationship: 'units'
            )
            ->create();

        $serials = $product->serialNumbers()->get();

        //Other system with same 3 serials
        System::factory()
            ->hasAttached(
                Product::factory()->times(3),
                fn($a) => ['serial_number' => $serials->shift()->serial_number],
                relationship: 'units'
            )
            ->create();

        $this->expectExceptionMessage($this->exceptionMessage);

        $serial = $s->units()->first()->unit->serial_number;

        //creating system only with one of three serial
        $this->execute([$serial]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionMessage = __('The set of entered serial numbers is incorrect.');
        $this->service = resolve(SystemService::class);
    }
}
