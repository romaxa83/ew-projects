<?php

namespace App\Services\Admins;

use App\Dto\Admins\AdminDto;
use App\Dto\PhoneDto;
use App\Enums\Permissions\AdminRolesEnum;
use App\Exceptions\Admins\AdminUniqPhoneEmailException;
use App\Exceptions\Admins\CantDeleteSuperAdminException;
use App\Models\Admins\Admin;
use App\Notifications\Admins\ChangePasswordNotification;
use App\Notifications\Admins\RecoverPasswordNotification;
use App\Notifications\Admins\SendPasswordNotification;
use Core\Exceptions\TranslatedException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class AdminService
{
    public function create(AdminDto $dto): Admin
    {
        return $this->editAdmin($dto, new Admin());
    }

    public function update(AdminDto $dto, Admin $admin): Admin
    {
        return $this->editAdmin($dto, $admin);
    }

    private function editAdmin(AdminDto $dto, Admin $admin): Admin
    {
        $this->checkUniqAdmin($dto, $admin);

        $admin->first_name = $dto->getFirstName();
        $admin->last_name = $dto->getLastName();
        $admin->second_name = $dto->getSecondName();
        $admin->email = $dto->getEmail();
        $admin->lang = $dto->getLang();

        $this->setPassword($admin, $dto->getPassword());

        if ($admin->isDirty()) {
            $admin->save();
        }

        $this->setPhones($dto->getPhones(), $admin);
        $admin->syncRoles($dto->getRoleId());

        return $admin->refresh();
    }

    private function checkUniqAdmin(AdminDto $dto, Admin $admin): void
    {
        $same = Admin::query()
            ->where('id', '<>', $admin->id)
            ->where('email', $dto->getEmail())
            ->first();

        if (!$same) {
            return;
        }
        throw new AdminUniqPhoneEmailException($same->getName());
    }

    /**
     * @param PhoneDto[] $phones
     * @param Admin $admin
     */
    private function setPhones(array $phones, Admin $admin): void
    {
        $admin
            ->phones()
            ->delete();

        $admin->phones()
            ->createMany(
                array_map(
                    fn(PhoneDto $phoneDto) => [
                        'phone' => $phoneDto->getPhone(),
                        'is_default' => $phoneDto->isDefault()
                    ],
                    $phones
                )
            );
    }

    private function setPassword(Admin $admin, ?string $password): void
    {
        if ($admin->password && !$password) {
            return;
        }
        $registration = empty($admin->password);

        $password = $password ?? Str::random(8);

        $admin->setPassword($password);
        $admin->save();

        $admin->notify(
            $registration ? new SendPasswordNotification($password) : new ChangePasswordNotification($password)
        );
    }

    public function delete(Admin $admin): bool
    {
        if ($admin->role->name === AdminRolesEnum::SUPER_ADMIN) {
            throw new CantDeleteSuperAdminException();
        }

        return $admin->delete();
    }

    public function show(array $args, array $relation): LengthAwarePaginator
    {
        return Admin::filter($args)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function recoverPassword(string $email): bool
    {
        $admin = Admin::whereEmail($email)->first();

        if (!$admin) {
            return false;
        }

        $validaMinutes = 30;
        $validUntil = now()->addMinutes($validaMinutes);

        $admin->recover_password_expires_at = $validUntil;
        $admin->save();

        $link = $this->getLinkForEmailReset(
            $admin,
            config('front_routes.set-password-page'),
            $validUntil->getTimestamp()
        );

        $admin->notify(new RecoverPasswordNotification($link));

        return true;
    }

    private function getLinkForEmailReset(Admin $admin, string $link, int $validUntil): string
    {
        $link = trim($link, '/');

        return $link . '?' . http_build_query(
                [
                    'token' => Crypt::encryptString(arrayToJson([
                        'id' => $admin->id,
                        'time' => $validUntil,
                    ])),
                    'valid_until' => $validUntil,
                ]
            );
    }

    public function setNewPassword(string $token, string $password): bool
    {
        $data = jsonToArray(Crypt::decryptString($token));

        $admin = Admin::whereKey($data['id'])->firstOrFail();

        if ($admin->recover_password_expires_at < now()) {
            throw new TranslatedException(trans('exceptions.expired-link'));
        }

        $this->setPassword($admin, $password);
        $admin->recover_password_expires_at = null;
        $admin->save();

        return true;
    }


}
