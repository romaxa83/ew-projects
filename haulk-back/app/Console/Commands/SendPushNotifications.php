<?php

namespace App\Console\Commands;

use App\Models\Orders\Order;
use App\Models\PushNotifications\PushNotificationTask;
use App\Models\Users\User;
use App\Notifications\Alerts\AlertNotification;
use App\Notifications\PushNotification;
use Exception;
use Illuminate\Console\Command;
use Lang;

class SendPushNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send_push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // TODO: delete old records ???

        $tasks = PushNotificationTask::where([
            ['is_sent', false],
            ['retry', '<=', PushNotificationTask::RETRY_COUNT],
            ['when', '<=', now()->timestamp],
        ])->get();

        $tasks->each(function (PushNotificationTask $task) {
            try {
                $user = User::find($task->user_id);

                if ($user && $task->isFirstTry() && $task->needOrderManagerAlert()) {
                    $order = $task->order_id ? Order::find($task->order_id) : null;

                    if ($order) {
                        $user->notify(
                            new AlertNotification(
                                $user->getCompanyId(),
                                'push.' . $task->type,
                                AlertNotification::TARGET_TYPE_ORDER,
                                ['order_id' => $order->id,],
                                ['load_id' => $order->load_id]
                            )
                        );
                    }
                }

                if ($user && $user->routeNotificationForFcm()) {
                    $user->notify(new PushNotification($task));

                    if ($task->is_hourly) {
                        $task->when += PushNotificationTask::SECONDS_IN_HOUR;
                    } else {
                        $task->is_sent = true;
                    }
                } else {
                    ++$task->retry;
                }

                $task->save();
            } catch (Exception $e) {
                ++$task->retry;
                $task->save();

                $this->error($e->getMessage());
            }
        });

        return Command::SUCCESS;
    }
}
