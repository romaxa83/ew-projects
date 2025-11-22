<?php

namespace Tests\Unit\Sms;

use App\Notifications\Members\MemberPhoneVerificationSms;
use App\ValueObjects\Phone;
use Core\Facades\Sms;
use Tests\TestCase;

class SmsFacadeTest extends TestCase
{
    public function test_facade_accessor(): void
    {
        Sms::fake();
        $phone = new Phone('14055555555');
        $sms = new MemberPhoneVerificationSms('123');
        Sms::to($phone)->send($sms);
        Sms::assertQueued(MemberPhoneVerificationSms::class);
    }
}
