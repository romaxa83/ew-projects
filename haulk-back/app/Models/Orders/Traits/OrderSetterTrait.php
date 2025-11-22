<?php

namespace App\Models\Orders\Traits;

use App\Dto\Contacts\ContactDto;
use App\Dto\Contacts\TimeDto;
use App\Http\Controllers\Api\Helpers\DateTimeHelper;
use App\Models\Orders\Order;
use App\Models\Users\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @mixin Order
 */
trait OrderSetterTrait
{
    public function setLoadId(string $loadId): self
    {
        $this->load_id = $loadId;
        return $this;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param User|int $user
     * @return OrderSetterTrait|Order
     */
    public function setUser($user): self
    {
        $this->user_id = is_int($user) ? $user : $user->id;
        return $this;
    }

    public function setPublicToken(?string $token = null): self
    {
        $this->public_token = $token ?? hash('sha256', Str::random(60));
        return $this;
    }

    public function setDispatcherId(?int $dispatcherId): self
    {
        $this->dispatcher_id = $dispatcherId;
        return $this;
    }

    public function setDriverId(?int $driverId): self
    {
        $this->driver_id = $driverId;
        return $this;
    }

    public function setInspectionType(int $inspectionType): self
    {
        $this->inspection_type = $inspectionType;
        return $this;
    }

    public function setInstructions(?string $instructions): self
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function setDispatchInstructions(?string $dispatchInstructions): self
    {
        $this->dispatch_instructions = $dispatchInstructions;
        return $this;
    }

    public function setNeedReview(bool $needReview): self
    {
        $this->need_review = $needReview;
        return $this;
    }

    public function setPickupDate(?Carbon $pickupDate): self
    {
        $this->pickup_date = $pickupDate ? $pickupDate->getTimestamp() : null;
        return $this;
    }

    public function setPickupBuyerNameNumber(?string $pickupBuyerNameNumber): self
    {
        $this->pickup_buyer_name_number = $pickupBuyerNameNumber;
        return $this;
    }

    public function setPickupTime(?TimeDto $time): self
    {
        $this->pickup_time = !$time ? null : [
            'from' => DateTimeHelper::toTime($time->from),
            'to' => DateTimeHelper::toTime($time->to)
        ];
        return $this;
    }

    public function setPickupComment(?string $comment): self
    {
        $this->pickup_comment = $comment;
        return $this;
    }

    public function setDeliveryDate(?Carbon $deliveryDate): self
    {
        $this->delivery_date = $deliveryDate ? $deliveryDate->getTimestamp() : null;
        return $this;
    }

    public function setDeliveryTime(?TimeDto $time): self
    {
        $this->delivery_time = !$time ? null : [
            'from' => DateTimeHelper::toTime($time->from),
            'to' => DateTimeHelper::toTime($time->to)
        ];
        return $this;
    }

    public function setDeliveryComment(?string $comment): self
    {
        $this->delivery_comment = $comment;
        return $this;
    }

    public function setShipperComment(?string $comment): self
    {
        $this->shipper_comment = $comment;
        return $this;
    }

    public function setPickupContact(ContactDto $dto): self
    {
        $this->pickup_contact = $dto->toArray();
        return $this;
    }

    public function setDeliveryContact(ContactDto $dto): self
    {
        $this->delivery_contact = $dto->toArray();
        return $this;
    }

    public function setShipperContact(ContactDto $dto): self
    {
        $this->shipper_contact = $dto->toArray();
        return $this;
    }
}
