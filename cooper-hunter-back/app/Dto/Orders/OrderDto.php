<?php

namespace App\Dto\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\ValueObjects\Phone;

class OrderDto
{

    private ?int $projectId;
    private string $serialNumber;

    private string $firstName;
    private string $lastName;
    private ?OrderStatusEnum $orderStatus = null;
    private Phone $phone;

    private ?string $comment;

    /**@var OrderPartDto[] $parts */
    private array $parts;

    private OrderShippingDto $shippingDto;

    private OrderPaymentDto $paymentDto;

    /**
     * @param array $args
     * @return OrderDto
     */
    public static function byArgs(array $args): OrderDto
    {
        $dto = new self();

        $dto->projectId = data_get($args, 'project_id');

        $dto->serialNumber = $args['serial_number'];

        $dto->lastName = $args['last_name'];
        $dto->firstName = $args['first_name'];
        $dto->phone = new Phone($args['phone']);
        $dto->comment = data_get($args, 'comment');

        if (!empty($args['status'])) {
            $dto->orderStatus = OrderStatusEnum::fromValue($args['status']);
        }

        $dto->shippingDto = OrderShippingDto::byArgs($args);

        foreach ($args['parts'] as $part) {
            $dto->parts[] = OrderPartDto::byArgs($part);
        }

        $payment = !empty($args['payment']) ? $args['payment'] : [];

        $dto->paymentDto = OrderPaymentDto::byArgs($payment);

        return $dto;
    }

    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getPhone(): Phone
    {
        return $this->phone;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return OrderStatusEnum|null
     */
    public function getOrderStatus(): ?OrderStatusEnum
    {
        return $this->orderStatus;
    }

    /**
     * @return OrderPartDto[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    public function getShipping(): OrderShippingDto
    {
        return $this->shippingDto;
    }

    public function getPayment(): OrderPaymentDto
    {
        return $this->paymentDto;
    }
}

