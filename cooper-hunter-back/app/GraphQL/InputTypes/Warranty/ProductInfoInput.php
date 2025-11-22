<?php

namespace App\GraphQL\InputTypes\Warranty;

use App\Entities\Warranty\WarrantyProductInfo;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class ProductInfoInput extends BaseInputType
{
    public const NAME = 'WarrantyProductInfoInput';

    public function fields(): array
    {
        return [
            'purchase_date' => [
                'type' => NonNullType::string(),
                'description' => 'Date in format: ' . WarrantyProductInfo::DATE_FORMAT,
                'rules' => ['string', 'date_format:' . WarrantyProductInfo::DATE_FORMAT],
            ],
            'installation_date' => [
                'type' => NonNullType::string(),
                'description' => 'Date in format: ' . WarrantyProductInfo::DATE_FORMAT,
                'rules' => ['string', 'date_format:' . WarrantyProductInfo::DATE_FORMAT],
            ],
            'installer_license_number' => [
                'type' => NonNullType::string(),
            ],
            'purchase_place' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
