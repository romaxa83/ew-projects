<?php

namespace App\GraphQL\Mutations\BackOffice\About\VideoManager;

use App\GraphQL\Types\FileType;
use App\Models\About\AboutCompany;
use App\Permissions\About\About\AboutCompanyUpdatePermission;
use App\Rules\Utils\VideoRule;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class AdditionalVideoUploadMutation extends BaseMutation
{
    public const NAME = 'aboutCompanyAdditionalVideoUpload';
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
            'video' => [
                'type' => FileType::nonNullType(),
                'rules' => [new VideoRule()],
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

        $about->addMedia($args['video'])
            ->toMediaCollection(AboutCompany::ADDITIONAL_MEDIA_SHORT_VIDEO);

        return true;
    }
}
