<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Solutions;

use App\Enums\Solutions\SolutionTypeEnum;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionIndoorEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionTypeEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;
use App\GraphQL\Types\NonNullType;
use App\Permissions\Catalog\Solutions\SolutionReadPermission;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class SolutionBtuListQuery extends BaseQuery
{
    public const NAME = 'solutionBtuList';
    public const PERMISSION = SolutionReadPermission::KEY;

    public function __construct(private SolutionService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'type' => [
                'type' => SolutionTypeEnumType::nonNullType(),
                'description' => 'Only INDOOR, OUTDOOR',
            ],
            'zone' => [
                'type' => SolutionZoneEnumType::type(),
                'description' => 'Required if type OUTDOOR',
            ],
            'indoor_type' => [
                'type' => SolutionIndoorEnumType::type(),
                'description' => 'Required if type INDOOR',
            ]
        ];
    }

    public function type(): Type
    {
        return NonNullType::listOf(
            NonNullType::int()
        );
    }

    public function rules(array $args = []): array
    {
        return [
            'type' => [
                'required',
                Rule::in(
                    [
                        SolutionTypeEnum::OUTDOOR,
                        SolutionTypeEnum::INDOOR
                    ]
                )
            ],
            'zone' => [
                'string',
                Rule::requiredIf(
                    fn() => $args['type'] === SolutionTypeEnum::OUTDOOR
                )
            ],
            'indoor_type' => [
                'string',
                Rule::requiredIf(
                    fn() => $args['type'] === SolutionTypeEnum::INDOOR
                )
            ]
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        /**
         * Клиент попросил выдать ему полный список BTU без привязок к категориям
         *
         * @link https://wezom.worksection.com/project/292109/10699681/10742831/
         */
        return $this->service->getAllBtu();
        //return $this->service->getBtuList($args);
    }

}
