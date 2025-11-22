<?php

namespace App\Dto\Commercial;

class CommercialQuoteAdminDto
{
    private ?string $status;
    private bool $hasStatus = false;

    private ?bool $sendDetailData;
    private bool $hasSendDetailData = false;

    public ?float $shippingPrice;
    private bool $hasShippingPrice = false;

    public ?float $tax;
    private bool $hasTax = false;

    public ?float $discountPercent;
    private bool $hasDiscountPercent = false;

    public ?float $discountSum;
    private bool $hasDiscountSum = false;

    public ?string $email;
    private bool $hasEmail = false;

    public array $items = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        if(array_key_exists('status', $args)){
            $dto->status = $args['status'];
            $dto->hasStatus = true;
        }
        if(array_key_exists('send_detail_data', $args)){
            $dto->sendDetailData = $args['send_detail_data'];
            $dto->hasSendDetailData = true;
        }
        if(array_key_exists('shipping_price', $args)){
            $dto->shippingPrice = $args['shipping_price'];
            $dto->hasShippingPrice = true;
        }
        if(array_key_exists('tax', $args)){
            $dto->tax = $args['tax'];
            $dto->hasTax = true;
        }
        if(array_key_exists('discount_percent', $args)){
            $dto->discountPercent = $args['discount_percent'];
            $dto->hasDiscountPercent = true;
        }
        if(array_key_exists('discount_sum', $args)){
            $dto->discountSum = $args['discount_sum'];
            $dto->hasDiscountSum = true;
        }
        if(array_key_exists('email', $args)){
            $dto->email = $args['email'];
            $dto->hasEmail = true;
        }

        foreach ($args['items'] ?? [] as $item) {
            $dto->items[] = CommercialQuoteItemDto::byArgs($item);
        }

        return $dto;
    }

    public function hasStatus(): bool
    {
        return $this->hasStatus;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function hasSendDetailData(): bool
    {
        return $this->hasSendDetailData;
    }

    public function getSendDetailData(): bool
    {
        return $this->sendDetailData;
    }

    public function hasShippingPrice(): bool
    {
        return $this->hasShippingPrice;
    }

    public function hasDiscountPercent(): bool
    {
        return $this->hasDiscountPercent;
    }

    public function hasDiscountSum(): bool
    {
        return $this->hasDiscountSum;
    }

    public function hasTax(): bool
    {
        return $this->hasTax;
    }

    public function hasEmail(): bool
    {
        return $this->hasEmail;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}

