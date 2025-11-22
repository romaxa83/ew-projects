<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings;

use App\Models\Commercial\CommercialSettings;
use App\Permissions\Commercial\CommercialSettings\CommercialSettingsUpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class CommercialSettingsPdfDeleteMutation extends BaseMutation
{
    public const NAME = 'commercialSettingsPdfDelete';
    public const PERMISSION = CommercialSettingsUpdatePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $settings = CommercialSettings::firstOrFail();

        $settings->clearMediaCollection($settings::MEDIA_PDF);

        return true;
    }
}
