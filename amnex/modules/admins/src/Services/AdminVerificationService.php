<?php

declare(strict_types=1);

namespace Wezom\Admins\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use JetBrains\PhpStorm\ArrayShape;
use RuntimeException;
use Throwable;
use Wezom\Admins\Dto\AdminDto;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Notifications\AdminEmailVerificationNotification;
use Wezom\Admins\Notifications\AdminForgotPasswordNotification;
use Wezom\Admins\Notifications\AdminSetPasswordNotification;
use Wezom\Core\Models\Auth\BaseAuthenticatable;
use Wezom\Core\Models\Auth\PersonalAccessToken;
use Wezom\Core\Traits\VerificationCodeGenerator;

class AdminVerificationService
{
    use VerificationCodeGenerator;

    /**
     * @throws Exception
     */
    public function sendSetPasswordLink(Admin $admin): void
    {
        $validUntil = now()->addMinutes(config('auth.admin_password_set_link_expires_in'));

        $frontUrl = $this->linkSetPasswordForAdmin($admin, $validUntil);

        $notification = new AdminSetPasswordNotification(
            $admin,
            $frontUrl
        );

        Notification::route('mail', $admin->email)->notify($notification);
    }

    /**
     * @throws Exception
     */
    public function sendResetLink(Admin $admin): void
    {
        $validUntil = now()->addMinutes(config('auth.admin_password_set_link_expires_in'));

        $frontUrl = $this->linkSetPasswordForAdmin($admin, $validUntil);

        $notification = new AdminForgotPasswordNotification(
            $admin,
            $frontUrl
        );

        Notification::route('mail', $admin->email)->notify($notification);
    }

    /**
     * @throws Exception
     */
    private function linkSetPasswordForAdmin(Admin $admin, Carbon $validUntil): string
    {
        return route('admin.reset-password', [
            'token' => $this->encryptTokenForResetPassword($admin, $validUntil->getTimestamp()),
            'valid_until' => $validUntil,
        ]);
    }

    /**
     * @throws Exception
     */
    public function fillEmailVerificationCode(Admin $admin): void
    {
        $admin->email_verification_code = $this->generateVerificationCode();
        $admin->save();
    }

    /**
     * @throws Exception
     */
    public function fillNewEmailVerificationCode(Admin $admin): void
    {
        $admin->new_email_verification_code = $this->generateVerificationCode();
        $admin->save();
    }

    /**
     * @throws Exception
     */
    public function encryptTokenForResetPassword(Admin $admin, int $validUntil): string
    {
        try {
            $this->fillEmailVerificationCode($admin);

            $body = [
                'id' => $admin->id,
                'time' => $validUntil,
                'code' => $admin->email_verification_code,
            ];

            return Crypt::encryptString(array_to_json($body));
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function sendLinkForVerificationEmail(Admin $admin): void
    {
        $validUntil = now()->addMinutes(config('auth.admin_email_verification_link_expires_in'));

        $frontUrl = $this->getLinkForEmailAdmin($admin, $validUntil);

        $admin->toggleStatus();
        $admin->save();

        $notification = new AdminEmailVerificationNotification($admin, $frontUrl);

        Notification::route('mail', $admin->new_email_for_verification)->notify($notification);
    }

    public function encryptTokenForEmailVerification(Admin $admin, int $validUntil): string
    {
        try {
            $this->fillNewEmailVerificationCode($admin);

            $body = [
                'id' => $admin->id,
                'time' => $validUntil,
                'code' => $admin->new_email_verification_code,
            ];

            return Crypt::encryptString(array_to_json($body));
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getLinkForEmailAdmin(Admin $admin, Carbon $validUntil): string
    {
        return route('admin.email-verification', [
            'token' => $this->encryptTokenForEmailVerification($admin, $validUntil->getTimestamp()),
            'valid_until' => $validUntil,
        ]);
    }

    /**
     * @throws Exception
     */
    public function checkAndSendVerificationEmail(Admin $admin, AdminDto $adminDto): void
    {
        if ($adminDto->email !== $admin->email) {
            if ($admin->email_verification_code) {
                $admin->email = $adminDto->email;
                $this->sendSetPasswordLink($admin);
            } else {
                $admin->new_email_for_verification = $adminDto->email;
                $this->sendLinkForVerificationEmail($admin);
                $this->revokeToken($admin);
            }
            $admin->save();
        }
    }

    public function revokeToken(Admin $admin): void
    {
        foreach ($admin->tokens()->get() as $token) {
            /** @var PersonalAccessToken $token */
            $token->revoke();
            $token->clearInCache();
        }
    }

    #[ArrayShape(['id' => 'int', 'time' => 'string', 'code' => 'string'])]
    public function decryptTokenForEmailReset(string $token): array
    {
        try {
            return json_to_array(Crypt::decryptString($token));
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function verifyEmailByCode(BaseAuthenticatable&Admin $admin, string $code): bool
    {
        $this->checkEmailVerified($admin);

        if ($admin->email_verification_code !== $code) {
            return false;
        }

        $this->cleanEmailVerificationCode($admin);

        return true;
    }

    public function cleanEmailVerificationCode(Admin $admin): void
    {
        $admin->email_verification_code = null;
        $admin->toggleStatus();
        $admin->save();
    }

    public function emailVerification(
        Admin $admin
    ): void {
        $admin->email = $admin->new_email_for_verification;
        $admin->new_email_for_verification = null;
        $admin->new_email_verification_code = null;
        $admin->new_email_verification_code_at = now();
        $admin->save();
    }

    /**
     * @throws Exception
     */
    protected function checkEmailVerified(Admin $admin): void
    {
        if ($admin->isEmailVerified()) {
            throw new Exception(__('admins::exceptions.email_already_verified'));
        }
    }
}
