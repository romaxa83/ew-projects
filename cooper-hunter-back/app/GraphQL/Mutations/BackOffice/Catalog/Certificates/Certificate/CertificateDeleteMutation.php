<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Certificate;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\Certificate;
use App\Permissions\Catalog\Certificates\Certificate\DeletePermission;
use App\Repositories\Catalog\Certificates\CertificateRepository;
use App\Services\Catalog\Certificate\CertificateService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class CertificateDeleteMutation extends BaseMutation
{
    public const NAME = 'certificateDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(
        protected CertificateService $service,
        protected CertificateRepository $repo
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
            /** @var $model Certificate */
            $model = $this->repo->getByFields(['id' => $args['id']]);
            $this->service->remove($model);

            return ResponseMessageEntity::success(__('messages.catalog.certificate.certificate.actions.delete.success.one-entity'));
        } catch (\Throwable) {
            return ResponseMessageEntity::fail(__('Oops, something went wrong!'));
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Certificate::TABLE, 'id')],
        ];
    }
}



