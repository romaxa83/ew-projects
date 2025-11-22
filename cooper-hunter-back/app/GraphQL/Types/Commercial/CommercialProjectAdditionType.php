<?php

namespace App\GraphQL\Types\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProjectAddition;
use Core\Traits\Auth\AuthGuardsTrait;
use GraphQL\Type\Definition\Type;

class CommercialProjectAdditionType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'CommercialProjectAdditionType';
    public const MODEL = CommercialProjectAddition::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'purchase_place' => [
                'type' => NonNullType::string(),
            ],
            'can_update' => [
                'type' => Type::boolean(),
                'selectable' => false,
                'resolvableField' => 'can_update',
                'resolve' => static fn(CommercialProjectAddition $m) => $m->can_update,
            ],
            'installer_license_number' => [
                'type' => NonNullType::string(),
            ],
            'installation_date' => [
                'type' => Type::string(),
                'resolve' => static fn(CommercialProjectAddition $p): ?string => $p->installation_date?->format(
                    DatetimeEnum::DATE
                ),
                'description' => 'Value in Y-m-d format',
            ],
            'purchase_date' => [
                'type' => Type::string(),
                'resolve' => static fn(CommercialProjectAddition $p): string => $p->purchase_date?->format(
                    DatetimeEnum::DATE
                ),
                'description' => 'Value in Y-m-d format',
            ],
        ];
    }
}
