<?php

namespace App\GraphQL\Mutations\BackOffice\Utilities\Media;

use App\Enums\Media\MediaModelsEnum;
use App\Permissions\Catalog\Categories\CategoryImageUploadPermission;
use App\Permissions\Catalog\Products\ProductImageUploadPermission;
use App\Permissions\Utilities\Media\ManageMediaPermission;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

abstract class BaseMediaMutation extends BaseMutation
{
    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return match ($args['model_type']) {
            MediaModelsEnum::CATEGORY => $this->can(CategoryImageUploadPermission::KEY),
            MediaModelsEnum::PRODUCT => $this->can(ProductImageUploadPermission::KEY),
            default => $this->can(ManageMediaPermission::KEY),
        };
    }

    public function type(): Type
    {
        return Type::boolean();
    }
}
