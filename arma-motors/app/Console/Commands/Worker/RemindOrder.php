<?php

namespace App\Console\Commands\Worker;

use App\Events\Firebase\FcmPush;
use App\Repositories\Order\OrderRepository;
use App\Services\Firebase\FcmAction;
use App\Services\Telegram\TelegramDev;
use App\Types\Order\Status;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class RemindOrder extends Command
{
    protected $signature = 'am:worker:remind-order';

    protected $description = 'Отправляет пуш уведомление для напоминания о назначеной заявки';

    public function handle(OrderRepository $repository)
    {
        $minutes = config('app.remind_order');

        $from = CarbonImmutable::now();
        $to = $from->addMinutes($minutes + 5);

        $orders = $repository->getForRemind($from, $to, [
            Status::CREATED, Status::IN_PROCESS
        ], [
            'additions', 'user'
        ]);

        if($orders->isNotEmpty()){
            TelegramDev::info("REMIND ORDER COUNT - [{$orders->count()}]", "system", TelegramDev::LEVEL_IMPORTANT);
        }

        foreach ($orders as $order){
            event(new FcmPush(
                $order->user,
                FcmAction::create(FcmAction::ORDER_REMIND, [
                    'class' => FcmAction::MODEL_ORDER,
                    'id' => $order->id
                ], $order),
                $order
            ));

            $order->additions->update(['is_send_remind' => true]);
        }
    }
}

