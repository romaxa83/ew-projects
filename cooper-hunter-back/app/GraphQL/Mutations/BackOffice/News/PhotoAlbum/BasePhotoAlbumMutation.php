<?php

namespace App\GraphQL\Mutations\BackOffice\News\PhotoAlbum;

use App\Permissions\News\NewsUpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\Type;

abstract class BasePhotoAlbumMutation extends BaseMutation
{
    public const PERMISSION = NewsUpdatePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }
}
