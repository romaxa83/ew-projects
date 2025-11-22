<?php

namespace App\GraphQL\Mutations\BackOffice\About\VideoManager;

use App\GraphQL\Types\FileType;
use App\Models\About\AboutCompany;
use App\Permissions\About\About\AboutCompanyUpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class AdditionalVideoPreviewUploadMutation extends BaseMutation
{
    public const NAME = 'aboutCompanyAdditionalVideoPreviewUpload';
    public const PERMISSION = AboutCompanyUpdatePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'image' => [
                'type' => FileType::nonNullType(),
                'rules' => ['image'],
            ]
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $about = AboutCompany::firstOrFail();

        $about->addMedia($args['image'])
            ->toMediaCollection(AboutCompany::ADDITIONAL_VIDEO_PREVIEW);

        return true;
    }
}
