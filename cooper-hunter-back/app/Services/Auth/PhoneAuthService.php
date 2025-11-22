<?php

namespace App\Services\Auth;

use App\Contracts\Members\Member;
use App\Dto\Auth\SmsConfirmTokenDto;
use App\Entities\Auth\PhoneTokenEntity;
use App\Exceptions\Auth\PhoneAlreadyVerifiedException;
use App\Exceptions\Auth\SmsAlreadySentException;
use App\Exceptions\Auth\SmsTokenException;
use App\Models\Auth\MemberPhoneVerification;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Notifications\Members\MemberPhoneVerificationSms;
use App\Traits\Auth\CodeGenerator;
use App\ValueObjects\Phone;
use Core\Facades\Sms;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

class PhoneAuthService
{
    use CodeGenerator;

    public function confirmNewPhone(
        Member $member,
        string $smsAccessToken
    ): bool {
        try {
            $code = $this->assertSmsTokenValid($smsAccessToken);

            $member->phone = $code->phone;
            $member->phone_verified_at = now();

            $code->delete();

            return true;
        } catch (SmsTokenException) {
            return false;
        }
    }

    /** @throws SmsTokenException */
    protected function assertSmsTokenValid(string $smsAccessToken
    ): MemberPhoneVerification {
        try {
            $code = MemberPhoneVerification::query()
                ->where('access_token', $smsAccessToken)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            logger('SMS access token is missing', ['token' => $smsAccessToken]);

            throw new SmsTokenException('SMS access token is missing');
        }

        if ($code->access_token_expires_at < now()) {
            logger('SMS access token is expired', ['token' => $smsAccessToken]);

            throw new SmsTokenException('SMS access token is expired');
        }

        return $code;
    }

    public function confirmSmsToken(SmsConfirmTokenDto $dto): PhoneTokenEntity
    {
        $code = MemberPhoneVerification::query()
            ->where('sms_token', $dto->getToken())
            ->where('code', $dto->getCode())
            ->firstOrFail();

        $code->access_token = $this->generateSecureToken();
        $code->access_token_expires_at =
            now()->addSeconds(config('auth.sms.access_token_lifetime'));
        $code->save();

        return new PhoneTokenEntity(
            $code->access_token,
            $code->access_token_expires_at->timestamp,
        );
    }

    protected function generateSecureToken(): string
    {
        return (string)Uuid::uuid4();
    }

    /** @throws Exception */
    public function sendPhoneCode(Phone $phone): PhoneTokenEntity
    {
        $this->assertPhoneNotVerified($phone);

        $this->assertCodeNotSentYet($phone);

        $code = $this->generateCodeForPhone($phone);

        Sms::to($phone)->send(new MemberPhoneVerificationSms($code->code));

        return new PhoneTokenEntity(
            $code->sms_token,
            $code->sms_token_expires_at->timestamp,
        );
    }

    protected function assertPhoneNotVerified(Member|Phone $member): void
    {
        if ($member instanceof Phone) {
            $user = User::query()->where('phone', $member)->first(
                'phone_verified_at'
            );
            $technician = Technician::query()->where('phone', $member)->first(
                'phone_verified_at'
            );

            $this->assertMemberPhoneNotVerified($user);
            $this->assertMemberPhoneNotVerified($technician);

            return;
        }

        $this->assertMemberPhoneNotVerified($member);
    }

    protected function assertMemberPhoneNotVerified(?Member $member): void
    {
        if (!$member) {
            return;
        }

        if ($member->phoneVerified()) {
            throw new PhoneAlreadyVerifiedException(
                __('Phone already verified!')
            );
        }
    }

    private function assertCodeNotSentYet(Phone $phone): void
    {
        $exists = MemberPhoneVerification::query()
            ->where('phone', $phone)
            ->where('sms_token_expires_at', '>', now())
            ->exists();

        if ($exists) {
            throw new SmsAlreadySentException(__('Sms already sent'));
        }
    }

    public function getTokenExpirationAt(): Carbon
    {
        return now()->addSeconds(config('auth.sms.token_lifetime'));
    }

    /** @throws Exception */
    protected function generateCodeForPhone(Phone $phone
    ): MemberPhoneVerification {
        $code = new MemberPhoneVerification();

        $code->phone = $phone;
        $code->code = $this->generateSmsCode();
        $code->sms_token = $this->generateSecureToken();
        $code->sms_token_expires_at = $this->getTokenExpirationAt();

        $code->save();

        return $code;
    }
}
