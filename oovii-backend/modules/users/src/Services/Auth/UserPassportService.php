<?php

namespace WezomCms\Users\Services\Auth;

class UserPassportService extends AuthPassportService
{
    public function getClientId(): int
    {
        return config('cms.users.users.oauth_client.users.id');
    }

    public function getClientSecret(): string
    {
        return config('cms.users.users.oauth_client.users.secret');
    }
}
