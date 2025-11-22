<?php

namespace App\ValueObjects\Orders;

use App\Documents\OrderDocument;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OverdueData
{
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_DELIVERY = 'delivery';
    public const TYPE_BROKER_PAYMENT = 'broker_payment';
    public const TYPE_CUSTOMER_PAYMENT = 'customer_payment';
    public const TYPE_BROKER_FEE_PAYMENT = 'broker_fee_payment';

    private const PRIORITIES = [
        self::TYPE_PICKUP => 1,
        self::TYPE_DELIVERY => 2,
        self::TYPE_BROKER_PAYMENT => 3,
        self::TYPE_CUSTOMER_PAYMENT => 4,
        self::TYPE_BROKER_FEE_PAYMENT => 5
    ];

    public const TYPES = [
        self::TYPE_PICKUP => self::TYPE_PICKUP,
        self::TYPE_DELIVERY => self::TYPE_DELIVERY,
        self::TYPE_BROKER_PAYMENT => 'payment',
        self::TYPE_CUSTOMER_PAYMENT => 'payment',
        self::TYPE_BROKER_FEE_PAYMENT => 'payment',
    ];

    private Collection $overdue;

    private function __construct()
    {
        $this->overdue = collect();
    }

    public static function make(): self
    {
        return new self();
    }

    private function getOverdueItems(): Collection
    {
        return $this
            ->overdue
            ->filter(
                static fn(array $item) => $item['name'] !== self::TYPE_BROKER_FEE_PAYMENT && $item['overdue']
            );
    }

    public function exists(): bool
    {
        return $this->getOverdueItems()->isNotEmpty();
    }

    public function forResource(): ?array
    {
        $overdue = $this->getOverdueItems()->sortBy('priority')->first();
        if (empty($overdue)) {
            return null;
        }
        return [
            'message' => $overdue['message'],
            'type' => $overdue['type']
        ];
    }

    private function add(string $type, Carbon $plannedDate): self
    {
        $now = Carbon::now()->setTimezone('UTC');
        $diff = $now->diffInDays($plannedDate);
        $this->overdue->put(
            $type,
            [
                'time' => $plannedDate,
                'name' => $type,
                'diff' => $diff,
                'message' => trans_choice('library.days_ago', $diff),
                'priority' => self::PRIORITIES[$type],
                'type' => self::TYPES[$type],
                'overdue' => $now->greaterThan($plannedDate)
            ]
        );
        return $this;
    }

    public function addPickup(Carbon $plannedDate): self
    {
        return $this->add(self::TYPE_PICKUP, $plannedDate);
    }

    public function addDelivery(Carbon $plannedDate): self
    {
        return $this->add(self::TYPE_DELIVERY, $plannedDate);
    }

    public function addBrokerPayment(Carbon $plannedDate): self
    {
        return $this->add(self::TYPE_BROKER_PAYMENT, $plannedDate);
    }

    public function addCustomerPayment(Carbon $plannedDate): self
    {
        return $this->add(self::TYPE_CUSTOMER_PAYMENT, $plannedDate);
    }

    public function addBrokerFeePayment(Carbon $plannedDate): self
    {
        return $this->add(self::TYPE_BROKER_FEE_PAYMENT, $plannedDate);
    }

    public function getPickupPlanedDate(): ?Carbon
    {
        return $this->getPlannedDate(self::TYPE_PICKUP);
    }

    private function getPlannedDate(string $type): ?Carbon
    {
        if (!$this->overdue->has($type)) {
            return null;
        }
        return $this->overdue->get($type)['time'];
    }

    public function getDeliveryPlanedDate(): ?Carbon
    {
        return $this->getPlannedDate(self::TYPE_DELIVERY);
    }

    public function getBrokerPaymentPlanedDate(): ?Carbon
    {
        return $this->getPlannedDate(self::TYPE_BROKER_PAYMENT);
    }

    public function getBrokerFeePaymentPlanedDate(): ?Carbon
    {
        return $this->getPlannedDate(self::TYPE_BROKER_FEE_PAYMENT);
    }

    public function getCustomerPaymentPlanedDate(): ?Carbon
    {
        return $this->getPlannedDate(self::TYPE_CUSTOMER_PAYMENT);
    }

    public static function makeFromDocument(OrderDocument $document): self
    {
        $data = self::make();
        if ($document->pickupPlannedDate) {
            $data->addPickup($document->pickupPlannedDate);
        }
        if ($document->deliveryPlannedDate) {
            $data->addDelivery($document->deliveryPlannedDate);
        }
        if ($document->customerPaymentPlannedDate) {
            $data->addCustomerPayment($document->customerPaymentPlannedDate);
        }
        if ($document->brokerPaymentPlannedDate) {
            $data->addBrokerPayment($document->brokerPaymentPlannedDate);
        }
        if ($document->brokerFeePaymentPlannedDate) {
            $data->addBrokerFeePayment($document->brokerFeePaymentPlannedDate);
        }
        return $data;
    }
}
