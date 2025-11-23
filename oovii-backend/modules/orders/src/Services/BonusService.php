<?php

namespace WezomCms\Orders\Services;


use WezomCms\Orders\Models\Order;
use WezomCms\Users\Enums\BonusHistoryType;

class BonusService
{
    public function createOrderBonusHistory(Order $order, int $bonus): void
    {
        $order->user
            ->inviterBonusHistory()
            ->create([
                'type' => BonusHistoryType::USE,
                'order_id' => $order->id,
                'bonus' => $bonus,
            ]);

        $order->user->updateBonusSum();
    }

    public function createReferralBonusHistory(Order $order): void
    {
        if ($order->user->referralBonusLeft()) {
            $order->user
                ->referralBonusHistory()
                ->create([
                    'type' => BonusHistoryType::ACCRUAL,
                    'inviter_id' => $order->user->ref_id,
                    'order_id' => $order->id,
                    'bonus' => $order->getBonusesSum(),
                ]);

            $order->user->inviter->updateBonusSum();
        }
    }
}
