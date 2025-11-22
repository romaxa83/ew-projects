<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Type;

use App\Dto\Catalog\Certificate\TypeDto;
use App\GraphQL\Types\Catalog\Certificates\CertificateTypeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\CertificateType;
use App\Permissions\Catalog\Certificates\Type\CreatePermission;
use App\Services\Catalog\Certificate\TypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CertificateTypeCreateMutation extends BaseMutation
{
    public const NAME = 'certificateTypeCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected TypeService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return CertificateTypeType::type();
    }

    public function args(): array
    {
        return [
            'type' => NonNullType::string(),
        ];
    }

    /** @throws Throwable */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): CertificateType
    {
        return makeTransaction(
            fn() => $this->service->create(
                TypeDto::byArgs($args)
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'type' => ['required', 'string'],
        ];
    }
}
