<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Type;

use App\Dto\Catalog\Certificate\TypeDto;
use App\GraphQL\Types\Catalog\Certificates\CertificateTypeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\CertificateType;
use App\Permissions\Catalog\Certificates\Type\UpdatePermission;
use App\Repositories\Catalog\Certificates\TypeRepository;
use App\Services\Catalog\Certificate\TypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CertificateTypeUpdateMutation extends BaseMutation
{
    public const NAME = 'certificateTypeUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected TypeService $service,
        protected TypeRepository $repo
    ) {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return CertificateTypeType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
            'type' => NonNullType::string(),
        ];
    }

    /** @throws Throwable */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): CertificateType
    {
        /** @var $model CertificateType */
        $model = $this->repo->getByFields(['id' => $args['id']]);
        return makeTransaction(
            fn() => $this->service->update(
                TypeDto::byArgs($args),
                $model
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(CertificateType::TABLE, 'id')],
            'type' => ['required', 'string'],
        ];
    }
}
