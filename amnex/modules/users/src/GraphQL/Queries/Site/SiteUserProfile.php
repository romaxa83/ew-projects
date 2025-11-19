<?php

namespace Wezom\Users\GraphQL\Queries\Site;

use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Users\Models\User;

class SiteUserProfile extends BaseFieldResolver
{
    public function resolve(Context $context): User
    {
        /** @var User */
        return $context->getUser();
    }
}
