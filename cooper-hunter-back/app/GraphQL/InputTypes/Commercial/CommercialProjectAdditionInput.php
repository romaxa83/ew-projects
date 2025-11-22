<?php

namespace App\GraphQL\InputTypes\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProject;
use Illuminate\Validation\Rule;

class CommercialProjectAdditionInput extends BaseInputType
{
    public const NAME = 'CommercialProjectAdditionInput';

    public function fields(): array
    {
        return [
            'commercial_project_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(CommercialProject::class, 'id')]
            ],
            'purchase_place' => [
                'type' => NonNullType::string(),
            ],
            'installer_license_number' => [
                'type' => NonNullType::string(),
            ],
            'installation_date' => [
                'type' => NonNullType::string(),
                'rules' => ['string', DatetimeEnum::DATE_RULE],
                'description' => 'Date in format Y-m-d H:i:s',
            ],
            'purchase_date' => [
                'type' => NonNullType::string(),
                'rules' => ['string', DatetimeEnum::DATE_RULE],
                'description' => 'Date in format Y-m-d H:i:s',
            ],
        ];
    }
}
