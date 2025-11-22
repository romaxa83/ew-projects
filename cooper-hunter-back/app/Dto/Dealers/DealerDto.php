<?php

namespace App\Dto\Dealers;

use App\Models\Users\User;
use App\Traits\GenerateRandomPassword;
use App\ValueObjects\Email;
use Illuminate\Support\Str;

class DealerDto
{
    use GenerateRandomPassword;

    public Email $email;
    public int $companyID;
    public string $name;
    public string $password;
    public ?string $companyName = null;
    public array $shippingAddressIDs = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->password = self::getPassword();
        $dto->email = new Email(data_get($args, 'email'));
        $dto->name = data_get($args, 'name');
        $dto->companyID = data_get($args, 'company_id');
        $dto->shippingAddressIDs = data_get($args, 'shipping_address_ids', []);

        return $dto;
    }
}
