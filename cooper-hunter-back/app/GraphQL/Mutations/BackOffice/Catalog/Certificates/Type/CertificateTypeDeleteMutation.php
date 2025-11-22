<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Type;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\CertificateType;
use App\Permissions\Catalog\Certificates\Type\DeletePermission;
use App\Repositories\Catalog\Certificates\TypeRepository;
use App\Services\Catalog\Certificate\TypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class CertificateTypeDeleteMutation extends BaseMutation
{
    public const NAME = 'certificateTypeDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(
        protected TypeService $service,
        protected TypeRepository $repo
    ) {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): ResponseMessageEntity
    {
        try {
            /** @var $model CertificateType */
            $model = $this->repo->getByFields(['id' => $args['id']]);
            $this->service->remove($model);

            return ResponseMessageEntity::success(__('messages.catalog.certificate.type.actions.delete.success.one-entity'));
        } catch (\Throwable) {
            return ResponseMessageEntity::fail(__('Oops, something went wrong!'));
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(CertificateType::TABLE, 'id')],
        ];
    }
}



