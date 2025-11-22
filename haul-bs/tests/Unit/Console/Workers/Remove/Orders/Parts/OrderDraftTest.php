<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Workers\Remove\Orders\Parts;

use App\Console\Workers\Remove\Orders\Parts\OrderDraft;
use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class OrderDraftTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_remove(): void
    {
        $time = CarbonImmutable::now()->subMinutes(config('workers.remove.orders.parts.draft_time') + 1);

        $this->orderBuilder->draft_at($time)->create();

        $this->artisan(OrderDraft::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseEmpty(Order::TABLE);
    }

    /** @test */
    public function it_not_remove(): void
    {
        $time = CarbonImmutable::now()->subMinutes(config('workers.remove.orders.parts.draft_time') - 1);

        $this->orderBuilder->draft_at($time)->create();

        $this->artisan(OrderDraft::class)
            ->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount(Order::TABLE, 1);
    }

    /** @test */
    public function it_null(): void
    {

        $this->orderBuilder->draft(false)->create();

        $this->artisan(OrderDraft::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseCount(Order::TABLE, 1);

    }
}
