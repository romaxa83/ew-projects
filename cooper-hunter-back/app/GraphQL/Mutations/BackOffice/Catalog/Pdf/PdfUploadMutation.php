<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Pdf;

use App\GraphQL\Types\Catalog\Pdf\PdfType;
use App\GraphQL\Types\FileType;
use App\Models\Catalog\Pdf\Pdf;
use App\Permissions\Catalog\Pdf\UploadPermission;
use App\Services\Catalog\Pdf\PdfService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class PdfUploadMutation extends BaseMutation
{
    public const NAME = 'pdfUpload';
    public const PERMISSION = UploadPermission::KEY;

    public function __construct(protected PdfService $service)
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
        return makeTransaction(
            fn() => $this->service->create($args['pdf'])
        );
    }
}

