<?php

namespace App\GraphQL\Mutations\BackOffice\Warranty\WarrantyInfo;

use App\Dto\Warranty\WarrantyInfo\WarrantyInfoDto;
use App\GraphQL\InputTypes\Warranty\WarrantyInfo\WarrantyInfoInput;
use App\GraphQL\Types\Warranty\WarrantyInfoType\WarrantyInfoType;
use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use App\Permissions\Warranty\WarrantyInfo\WarrantyInfoCreatePermission;
use App\Services\Warranty\WarrantyInfoService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class WarrantyInfoMutation extends BaseMutation
{
    public const NAME = 'warrantyInfo';
    public const PERMISSION = WarrantyInfoCreatePermission::KEY;

    public function __construct(protected WarrantyInfoService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'warranty_info' => [
                'type' => WarrantyInfoInput::nonNullType(),
            ],
        ];
    }

    public function type(): Type
    {
        return WarrantyInfoType::type();
    }

    /**
     * @throws Throwable
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): WarrantyInfo {
        return makeTransaction(
            fn() => $this->service->createOrUpdate(
                WarrantyInfoDto::byArgs($args['warranty_info'])
            ),
        );
    }
}
