<?php

namespace App\Listeners\Passport;

use App\Models\Users\AuthHistory;
use App\Services\Passport\PassportService;
use Laravel\Passport\Events\AccessTokenCreated;

class RevokeOldTokens
{
    private AuthHistory $authHistory;
    private PassportService $service;

    public function __construct(PassportService $service)
    {
        $this->service = $service;
        $this->authHistory = resolve(AuthHistory::class);
    }

    public function handle(AccessTokenCreated $event): void
    {
        $this->service->revokeRefreshTokens(
            $event->userId,
            $event->clientId,
            $event->tokenId
        );

        $this->service->revokeAccessTokens(
            $event->userId,
            $event->clientId,
            $event->tokenId
        );

        if ($event->clientId === config('auth.oauth_client.users.id')) {
            $this->trackLoginHistory($event->userId);
        }
    }

    private function trackLoginHistory(int $userId): void
    {
        # Save time when token revoked to history table
        $this->authHistory::whereUserId($userId)
            ->latest('id')
            ->limit(1)
            ->update(
                [
                    'exit_time' => now()
                ]
            );

        # Save last login time to history table
        $this->authHistory->user_id = $userId;
        $this->authHistory->ip = request()->header('X-Forwarded-For') ?? request()->ip();
        $this->authHistory->saveOrFail();
    }
}
