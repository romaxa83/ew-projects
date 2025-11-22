<?php

namespace App\Dto\Warranty;

use App\Dto\Warranty\WarrantyInfo\WarrantyAddressDto;
use App\Entities\Warranty\WarrantyProductInfo;
use App\Entities\Warranty\WarrantyUserInfo;
use App\Enums\Warranties\WarrantyType;
use Illuminate\Support\Arr;

class WarrantyRegistrationDto
{
    public WarrantyType $type;
    public $commercialProjectID;
    private WarrantyUserInfo $userInfo;
    private WarrantyAddressDto $addressDto;
    private WarrantyProductInfo $productInfo;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $info = $args['user'] ?? $args['technician'];
        $isUser = Arr::has($args, 'user');

        $self->userInfo = WarrantyUserInfo::make($info, $isUser);
        $self->addressDto = WarrantyAddressDto::byArgs($args['address']);
        $self->productInfo = WarrantyProductInfo::make($args['product']);

        $self->type = WarrantyType::fromValue($args['type'] ?? WarrantyType::RESIDENTIAL());
        $self->commercialProjectID = $args['commercial_project_id'] ?? null;

        return $self;
    }

    public function getUserInfo(): WarrantyUserInfo
    {
        return $this->userInfo;
    }

    public function getAddressDto(): WarrantyAddressDto
    {
        return $this->addressDto;
    }

    public function getProductInfo(): WarrantyProductInfo
    {
        return $this->productInfo;
    }
}
