<?php

namespace App\Console\Commands\Auth;

use App\Models\Auth\MemberPhoneVerification;
use Illuminate\Console\Command;

class RemoveExpiredSmsTokensCommand extends Command
{
    protected $signature = 'auth:remove-expired-sms-tokens';

    protected $description = 'Remove all expired sms tokens';

    public function handle(): int
    {
        MemberPhoneVerification::query()
            ->where('sms_token_expires_at', '<=', now())
            ->whereNull('access_token')
            ->delete();

        MemberPhoneVerification::query()
            ->where('access_token_expires_at', '<=', now())
            ->delete();

        return self::SUCCESS;
    }
}
