<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Pdf;

use App\GraphQL\Types\Catalog\Pdf\PdfType;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Pdf\Pdf;
use App\Permissions\Catalog\Pdf\UploadPermission;
use App\Repositories\Catalog\Pdf\PdfRepository;
use App\Services\Catalog\Pdf\PdfService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class PdfReUploadMutation extends BaseMutation
{
    public const NAME = 'pdfReUpload';
    public const PERMISSION = UploadPermission::KEY;

    public function __construct(
        protected PdfService $service,
        protected PdfRepository $repo
    )
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return PdfType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => "ID pdf"
            ],
            'pdf' => [
                'type' => FileType::nonNullType(),
            ],
        ];
    }

    protected function rules(array $args = []): array
    {
        return [
            'pdf' => ['required', 'mimes:pdf'],
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Pdf
    {
        /** @var Pdf $model */
        $model = $this->repo->getBy('id', $args['id']);

        return $this->service->rewrite($model, $args['pdf']);
    }
}
