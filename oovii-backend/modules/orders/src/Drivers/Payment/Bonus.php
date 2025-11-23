<?php

namespace WezomCms\Orders\Drivers\Payment;

use Auth;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\PaymentDriverInterface;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Events\AutoPayedOrder;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderPaymentInformation;
use WezomCms\Orders\Services\BonusService;

class Bonus implements PaymentDriverInterface
{
    public const KEY = 'bonus';

    public function __construct(private BonusService $bonusService)
    {
    }

    public function validatePayment(array $data = []): bool
    {
        $user = Auth::user();
        $cart = resolve(CartInterface::class);

        return $user
            ? $user->bonus >= $cart->total()
            : false;
    }

    public function handleOrder(Order $order): Order
    {
        $user = Auth::user();
        $useBonus = $order->getTotalSum();

        if ($user && $user->bonus >= $useBonus) {
            $order
                ->setPaid(PayedModes::AUTO)
                ->save();

            event(new AutoPayedOrder($order));

            $this->bonusService->createOrderBonusHistory($order, $useBonus);
        }

        return $order;
    }

    public function handleOrderPayment(Order $order): Order
    {
        return $order;
    }

    public function getValidationRules(): array
    {
        return [ [], [], [] ];
    }

    public function getPaymentPayload(): array
    {
        return [
            'payment_driver' => self::KEY,
        ];
    }

    public function renderPaymentData(OrderPaymentInformation $paymentInfo): string
    {
        return '';
    }
}
