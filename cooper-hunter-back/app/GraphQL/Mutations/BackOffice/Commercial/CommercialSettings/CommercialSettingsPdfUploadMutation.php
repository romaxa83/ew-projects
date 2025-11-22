<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings;

use App\GraphQL\Types\FileType;
use App\Models\Commercial\CommercialSettings;
use App\Permissions\Commercial\CommercialSettings\CommercialSettingsUpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class CommercialSettingsPdfUploadMutation extends BaseMutation
{
    public const NAME = 'commercialSettingsPdfUpload';
    public const PERMISSION = CommercialSettingsUpdatePermission::KEY;

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
            'pdf' => [
                'type' => FileType::nonNullType(),
                'rules' => ['file', 'mimes:pdf'],
            ]
        ];
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $settings = CommercialSettings::firstOrFail();

        $settings->addMedia($args['pdf'])
            ->toMediaCollection($settings::MEDIA_PDF);

        return true;
    }
}
