<?php

namespace App\Dto\Payments;

use App\Dto\Utilities\Address\AddressDto;
use App\Dto\Utilities\Morph\MorphDto;

class PaymentCardDto
{
    public string $type;
    public string $name;
    public string $number;
    public string $cvc;
    public string $expirationDate;

    public ?AddressDto $billingAddress = null;
    public ?MorphDto $morph = null;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->type = data_get($args, 'payment_card.type');
        $dto->name = data_get($args, 'payment_card.name');
        $dto->number = data_get($args, 'payment_card.number');
        $dto->cvc = data_get($args, 'payment_card.cvc');
        $dto->expirationDate = data_get($args, 'payment_card.expiration_date');

        if($address = data_get($args, 'billing_address')){
            $dto->billingAddress = AddressDto::byArgs($address);
        }
        if($morph = data_get($args, 'morph')){
            $dto->morph = MorphDto::byArgs($morph);
        }

        return $dto;
    }

    public function lastFourNumericForCode(): string
    {
        return substr(trim($this->number), -4);
    }

    public function hash(): string
    {
        $tmp = clear_str($this->type)
            . clear_str($this->number)
            . clear_str($this->cvc)
            . clear_str($this->expirationDate)
        ;

        return md5($tmp);
    }
}

