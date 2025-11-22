<?php


namespace App\Services\Orders;


use App\Models\Orders\Bonus;
use App\Models\Orders\Expense;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Support\Carbon;

class OrderPaymentService
{

    /**
     * @return static
     */
    public static function init(): self
    {
        return new self();
    }

    /**
     * @param Order $order
     * @param string $payer
     * @return array|int[]
     */
    public function getTotalPaymentForecast(Order $order, string $payer): array
    {
        $payment = $order->payment;

        $total = $payment ? ($payer === Payment::PAYER_BROKER ? $payment->broker_payment_amount : $payment->customer_payment_amount) : 0;

        if (!$total) {
            return [
                'expenses' => 0,
                'bonuses' => 0,
                'total' => 0
            ];
        }

        $expenses = $order->expenses()
            ->where('to', $payer)
            ->get()
            ->map(
                function (Expense $item) {
                    return [
                        'type' => $item->type_name,
                        'date' => Carbon::createFromTimestamp($item->date)->format(config('formats.pdf_date')),
                        'price' => $item->price
                    ];
                }
            );

        $bonuses = $order->bonuses()
            ->where('to', $payer)
            ->get()
            ->map(
                function (Bonus $bonus) {
                    return [
                        'type' => $bonus->type,
                        'price' => $bonus->price
                    ];
                }
            );

        return [
            'expenses' => $expenses,
            'bonuses' => $bonuses,
            'total' => (float)($total
                + $expenses->sum('price')
                + $bonuses->sum('price'))
        ];
    }

    /**
     * @param Payment $payment
     * @param int|null $pickupDate
     * @param int|null $deliveryDate
     * @return int|null
     */
    private function getBrokerPaymentPlanedDate(Payment $payment, ?int $pickupDate, ?int $deliveryDate): ?int
    {
        switch ($payment->broker_payment_begins) {
            case Order::LOCATION_PICKUP:
                return $pickupDate ? addBusinessDays($pickupDate, $payment->broker_payment_days)
                    ->endOfDay()
                    ->getTimestamp()
                    : null;
            case Order::LOCATION_DELIVERY:
                return $deliveryDate ? addBusinessDays($deliveryDate, $payment->broker_payment_days)
                    ->endOfDay()
                    ->getTimestamp()
                    : null;
            case Order::INVOICE_SENT:
                return $payment->broker_payment_invoice_issue_date
                    ? addBusinessDays($payment->broker_payment_invoice_issue_date, $payment->broker_payment_days)
                        ->endOfDay()
                        ->getTimestamp()
                    : null;
            default:
                return null;
        }
    }

    /**
     * @param Payment $payment
     * @param int|null $pickupDate
     * @param int|null $deliveryDate
     * @return int
     */
    private function getCustomerPaymentPlanedDate(Payment $payment, ?int $pickupDate, ?int $deliveryDate): ?int
    {
        $date = $payment->customer_payment_location === Order::LOCATION_PICKUP ? $pickupDate : $deliveryDate;

        if (empty($date)) {
            return null;
        }

        return Carbon::createFromTimestamp($date)->endOfDay()->getTimestamp();
    }

    /**
     * @param Payment $payment
     * @param int|null $pickupDate
     * @param int|null $deliveryDate
     * @return int|null
     */
    private function getBrokerFeePaymentPlanedDate(Payment $payment, ?int $pickupDate, ?int $deliveryDate): ?int
    {
        if ($payment->broker_fee_begins === Order::LOCATION_PICKUP) {
            return $pickupDate
                ? addBusinessDays($pickupDate, $payment->broker_fee_days)
                    ->endOfDay()
                    ->getTimestamp()
                : null;
        }

        return $deliveryDate
            ? addBusinessDays($deliveryDate, $payment->broker_fee_days)
                ->endOfDay()
                ->getTimestamp()
            : null;
    }

    /**
     * @param null|Payment $payment
     */
    public function updatePlannedDate(?Payment $payment): void
    {
        if (!$payment) {
            return;
        }

        $pickupDate = $payment->order->pickup_date_actual;
        $deliveryDate = $payment->order->delivery_date_actual;

        $payment->broker_payment_planned_date = $payment->broker_payment_amount ? $this->getBrokerPaymentPlanedDate($payment, $pickupDate, $deliveryDate) :  null;
        $payment->customer_payment_planned_date = $payment->customer_payment_amount ? $this->getCustomerPaymentPlanedDate($payment, $pickupDate, $deliveryDate) : null;
        $payment->broker_fee_planned_date = $payment->broker_fee_amount ? $this->getBrokerFeePaymentPlanedDate($payment, $pickupDate, $deliveryDate) : null;

        $payment->save();
    }
}
