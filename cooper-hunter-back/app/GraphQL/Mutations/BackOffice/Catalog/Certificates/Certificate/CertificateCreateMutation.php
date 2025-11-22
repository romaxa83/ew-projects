<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Certificate;

use App\Dto\Catalog\Certificate\CertificateDto;
use App\GraphQL\Types\Catalog\Certificates\CertificateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\Certificate;
use App\Permissions\Catalog\Certificates\Certificate\CreatePermission;
use App\Services\Catalog\Certificate\CertificateService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use App\Models\Catalog\Certificates\CertificateType as CertificateTypeModel;
use Throwable;

class CertificateCreateMutation extends BaseMutation
{
    public const NAME = 'certificateCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected CertificateService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return CertificateType::type();
    }

    public function args(): array
    {
        return [
            'number' => NonNullType::string(),
            'link' => Type::string(),
            'type_id' => NonNullType::id(),
        ];
    }

    /** @throws Throwable */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Certificate
    {
        return makeTransaction(
            fn() => $this->service->create(
                CertificateDto::byArgs($args)
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'number' => ['required', 'string'],
            'link' => ['nullable', 'string'],
            'type_id' => ['required', 'integer', Rule::exists(CertificateTypeModel::TABLE, 'id')],
        ];
    }
}
