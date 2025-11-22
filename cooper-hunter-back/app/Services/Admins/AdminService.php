<?php

namespace App\Services\Admins;

use App\Dto\Admins\AdminDto;
use App\Events\Admins\AdminCreatedEvent;
use App\Models\Admins\Admin;
use App\Services\Localizations\LocalizationService;
use Core\Chat\Models\Conversation;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class AdminService
{
    public function __construct(protected LocalizationService $localizationService)
    {
    }

    public function create(AdminDto $dto): Admin
    {
        $admin = new Admin();
        $admin->name = $dto->getName();
        $admin->email = $dto->getEmail();
        $admin->setPassword($dto->getPassword());

        $admin->lang = $this->localizationService->getDefaultSlug();
        $admin->save();

        event(new AdminCreatedEvent($admin, $dto->getPassword()));

        if ($dto->hasRoleId()) {
            $admin->assignRole($dto->getRoleId());
        }

        return $admin;
    }

    public function update(Admin $admin, AdminDto $dto): Admin
    {
        $admin->name = $dto->getName();
        $admin->email = $dto->getEmail();

        if ($dto->hasPassword()) {
            $admin->setPassword($dto->getPassword());
        }

        if ($dto->hasRoleId()) {
            $admin->syncRoles($dto->getRoleId());
        }

        if ($admin->isDirty()) {
            $admin->save();
        }

        return $admin;
    }

    public function changePassword(Authenticatable|Admin $admin, string $password): bool
    {
        return $admin->setPassword($password)
            ->save();
    }

    public function createNewPassword(): string
    {
        $digitsCount = 2;

        $source = Str::lower(Str::random(Admin::MIN_LENGTH_PASSWORD - $digitsCount));

        $digits = substr(str_shuffle('1234567890'), 0, $digitsCount);

        return str_shuffle($source . $digits);
    }

    public function delete(Admin $admin): bool
    {
        $admin->conversations()
            ->each(static fn(Conversation $c) => $admin->leaveConversation($c));

        return $admin->delete();
    }
}
