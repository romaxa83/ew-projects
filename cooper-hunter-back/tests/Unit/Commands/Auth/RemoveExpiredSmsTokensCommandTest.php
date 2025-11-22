<?php

namespace Tests\Unit\Commands\Auth;

use App\Console\Commands\Auth\RemoveExpiredSmsTokensCommand;
use App\Models\Auth\MemberPhoneVerification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RemoveExpiredSmsTokensCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_remove_sms_tokens(): void
    {
        MemberPhoneVerification::factory()->withAccessToken()->create();

        Carbon::setTestNow(now()->addHour()->addMinute());

        MemberPhoneVerification::factory()->withAccessToken()->create();

        $this->assertDatabaseCount(MemberPhoneVerification::TABLE, 2);

        $this->artisan(RemoveExpiredSmsTokensCommand::class);

        $this->assertDatabaseCount(MemberPhoneVerification::TABLE, 1);
    }
}
