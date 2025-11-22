<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Pdf;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Pdf\Pdf;
use App\Permissions\Catalog\Pdf\DeletePermission;
use App\Services\Catalog\Pdf\PdfService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class PdfDeleteMutation extends BaseMutation
{
    public const NAME = 'pdfDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(protected PdfService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->delete(
            Pdf::query()->findOrFail($args['id'])
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Pdf::TABLE, 'id')],
        ];
    }
}
