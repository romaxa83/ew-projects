<?php

namespace App\Repositories\Passport;

use Carbon\Carbon;

class OAuthRepository
{
    public function authUserRow($userId)
    {
        return \DB::table('oauth_access_tokens')
            ->where('user_id', $userId)
            ->where('expires_at', '>', Carbon::now())
            ->where('revoked', 0)
            ->first();
    }

    public function deleteRefreshTokenByAuthId($authId)
    {
        return \DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $authId)
            ->where('expires_at', '>', Carbon::now())
            ->where('revoked', 0)
            ->delete();
    }

    public function deleteAuthUserRow($userId)
    {
        return \DB::table('oauth_access_tokens')
            ->where('user_id', $userId)
            ->where('expires_at', '>', Carbon::now())
            ->where('revoked', 0)
            ->delete();
    }
}
