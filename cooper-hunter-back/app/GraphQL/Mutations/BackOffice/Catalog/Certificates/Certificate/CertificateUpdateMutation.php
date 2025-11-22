<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Certificate;

use App\Dto\Catalog\Certificate\CertificateDto;
use App\GraphQL\Types\Catalog\Certificates\CertificateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\Certificate;
use App\Permissions\Catalog\Certificates\Certificate\UpdatePermission;
use App\Repositories\Catalog\Certificates\CertificateRepository;
use App\Services\Catalog\Certificate\CertificateService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;
use App\Models\Catalog\Certificates\CertificateType as CertificateTypeModel;

class CertificateUpdateMutation extends BaseMutation
{
    public const NAME = 'certificateUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected CertificateService $service,
        protected CertificateRepository $repo
    ) {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return CertificateType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
            'number' => NonNullType::string(),
            'link' => Type::string(),
            'type_id' => NonNullType::id(),
        ];
    }

    /** @throws Throwable */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Certificate
    {
        /** @var $model Certificate */
        $model = $this->repo->getByFields(['id' => $args['id']]);
        return makeTransaction(
            fn() => $this->service->update(
                CertificateDto::byArgs($args),
                $model
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Certificate::TABLE, 'id')],
            'number' => ['required', 'string'],
            'link' => ['nullable', 'string'],
            'type_id' => ['required', 'integer', Rule::exists(CertificateTypeModel::TABLE, 'id')],
        ];
    }
}

