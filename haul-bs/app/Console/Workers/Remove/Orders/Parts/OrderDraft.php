<?php

namespace App\Console\Workers\Remove\Orders\Parts;

use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class OrderDraft extends Command
{
    protected $signature = 'worker:remove_order_draft';

    protected $description = 'Удаления старых черновиков для заказа';

    public function handle(): int
    {
        $duration = config('workers.remove.orders.parts.draft_time');
        $time = CarbonImmutable::now()->subMinutes($duration);

        try {
            Order::query()
                ->where('draft_at', '<', $time)
                ->each(fn (Order $order) => $order->forceDelete())
            ;

        } catch (\Throwable $e) {
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
