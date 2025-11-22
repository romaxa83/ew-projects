<?php

namespace App\GraphQL\Mutations\BackOffice\About\VideoManager;

use App\Models\About\AboutCompany;
use App\Permissions\About\About\AboutCompanyUpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class VideoPreviewDeleteMutation extends BaseMutation
{
    public const NAME = 'aboutCompanyVideoPreviewDelete';
    public const PERMISSION = AboutCompanyUpdatePermission::KEY;

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
        $about = AboutCompany::firstOrFail();

        $about->clearMediaCollection(AboutCompany::VIDEO_PREVIEW);

        return true;
    }
}
