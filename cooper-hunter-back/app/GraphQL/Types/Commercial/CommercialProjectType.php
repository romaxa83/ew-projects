<?php

namespace App\GraphQL\Types\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Commercial\Commissioning\ProjectProtocolType;
use App\GraphQL\Types\Enums\Commercial\CommercialProjectStatusEnumType;
use App\GraphQL\Types\Locations\CountryType;
use App\GraphQL\Types\Locations\StateType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Admins\Admin;
use App\Models\Commercial\CommercialProject;
use App\Models\Locations\State;
use Core\Traits\Auth\AuthGuardsTrait;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;

class CommercialProjectType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'CommercialProjectType';
    public const MODEL = CommercialProject::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'member' => [
                    'type' => UserMorphType::Type(),
//                    'type' => UserMorphType::nonNullType(),
                    'is_relation' => true,
                    'always' => 'id',
                    'resolve' => function (CommercialProject $p) {
                        // I'm not sure that I should do show code for members
                        return $p->member ?? null;
                    }
                ],
                'quotes' => [
                    'type' => CommercialQuoteType::list(),
                    'is_relation' => true
                ],
                'status' => [
                    'type' => CommercialProjectStatusEnumType::nonNullType(),
                ],
                'name' => [
                    'type' => NonNullType::string(),
                ],
                'code' => [
                    'type' => Type::string(),
                    'resolve' => function (CommercialProject $p) {
                        // I'm not sure that I should do show code for members
                        return $this->getAuthUser() instanceof Admin
                            ? $p->code
                            : null;
                    }
                ],
                'address_line_1' => [
                    'type' => NonNullType::string(),
                ],
                'address_line_2' => [
                    'type' => Type::string(),
                ],
                'city' => [
                    'type' => NonNullType::string(),
                ],
                'state' => [
                    'type' => StateType::type(),
                    'is_relation' => true,
                ],
                'country' => [
                    'type' => CountryType::type(),
                    'is_relation' => true,
                ],
                'zip' => [
                    'type' => NonNullType::string(),
                ],
                'first_name' => [
                    'type' => NonNullType::string(),
                ],
                'last_name' => [
                    'type' => NonNullType::string(),
                ],
                'phone' => [
                    'type' => NonNullType::string(),
                ],
                'email' => [
                    'type' => NonNullType::string(),
                ],
                'company_name' => [
                    'type' => NonNullType::string(),
                ],
                'company_address' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => Type::string(),
                ],
                'estimate_start_date' => [
                    'type' => NonNullType::string(),
                    'resolve' => static fn(CommercialProject $p): string => $p->estimate_start_date->format(
                        DatetimeEnum::DATE
                    ),
                    'description' => 'Value in Y-m-d format',
                ],
                'start_pre_commissioning_date' => [
                    'type' => Type::string(),
                    'resolve' => static fn(CommercialProject $p): ?string => $p->start_pre_commissioning_date?->format(
                        DatetimeEnum::DATE
                    ),
                    'description' => 'Value in Y-m-d format',
                ],
                'end_pre_commissioning_date' => [
                    'type' => Type::string(),
                    'resolve' => static fn(CommercialProject $p): ?string => $p->end_pre_commissioning_date?->format(
                        DatetimeEnum::DATE
                    ),
                    'description' => 'Value in Y-m-d format',
                ],
                'start_commissioning_date' => [
                    'type' => Type::string(),
                    'resolve' => static fn(CommercialProject $p): ?string => $p->start_commissioning_date?->format(
                        DatetimeEnum::DATE
                    ),
                    'description' => 'Value in Y-m-d format',
                ],
                'end_commissioning_date' => [
                    'type' => Type::string(),
                    'resolve' => static fn(CommercialProject $p): ?string => $p->end_commissioning_date?->format(
                        DatetimeEnum::DATE
                    ),
                    'description' => 'Value in Y-m-d format',
                ],
                'request_until' => [
                    'type' => Type::string(),
                    'resolve' => static fn(CommercialProject $p): ?string => $p->request_until?->format(
                        DatetimeEnum::DATE
                    ),
                    'description' => 'Value in Y-m-d format',
                ],
                'estimate_end_date' => [
                    'type' => NonNullType::string(),
                    'resolve' => static fn(CommercialProject $p): string => $p->estimate_end_date->format(
                        DatetimeEnum::DATE
                    ),
                    'description' => 'Value in Y-m-d format',
                ],
                'project_protocols' => [
                    'type' => Type::listOf(ProjectProtocolType::type()),
                    'is_relation' => true,
                    'always' => ['project_id', 'id'],
                    'alias' => 'projectProtocols',
                    'description' => 'All attach protocols',
                ],
                'project_protocols_pre_commissioning' => [
                    'type' => Type::listOf(ProjectProtocolType::type()),
                    'alias' => 'projectProtocolsPreCommissioning',
                    'description' => 'All attach protocols only for pre-commissioning'
                ],
                'project_protocols_commissioning' => [
                    'type' => Type::listOf(ProjectProtocolType::type()),
                    'alias' => 'projectProtocolsCommissioning',
                    'description' => 'All attach protocols only for commissioning'
                ],
                'additions' => [
                    'type' => CommercialProjectAdditionType::type(),
                    'is_relation' => true,
                ],
            ],
        );
    }
}
