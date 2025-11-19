<?php

namespace Wezom\Admins\Policies;

use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Enums\PermissionActionEnum;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\Permissions\Ability;

class AdminPolicy
{
    public function update(Admin $admin, Admin $resource): bool
    {
        if (!$admin->isSuperAdmin() && $resource->id !== $admin->id) {
            return false;
        }

        return $admin->can($this->ability(PermissionActionEnum::UPDATE))
            && $resource->status !== AdminStatusEnum::INACTIVE;
    }

    public function delete(Admin $admin, Admin $resource): bool
    {
        if (!$admin->can($this->ability(PermissionActionEnum::DELETE))) {
            return false;
        }

        if ($resource->id === $admin->id) {
            throw new TranslatedException(__('admin::messages.admin_fail_reasons_by_myself'));
        }

        return !$resource->isSuperAdmin()
            || $resource->status === AdminStatusEnum::INACTIVE;
    }

    public function changeStatus(Admin $admin, Admin $resource): bool
    {
        if (!$admin->can($this->ability(PermissionActionEnum::UPDATE))) {
            return false;
        }

        if ($admin->id === $resource->id) {
            throw new TranslatedException(__('admin::messages.admin_fail_reasons_by_myself'));
        }

        return true;
    }

    public function resendInvite(Admin $admin, Admin $resource): bool
    {
        if (!$admin->can($this->ability(PermissionActionEnum::UPDATE))) {
            return false;
        }

        if ($resource->isEmailVerified()) {
            throw new TranslatedException(
                __('admins::exceptions.this_admin_has_already_accepted_the_invitation')
            );
        }

        return true;
    }

    public function resendEmailVerification(Admin $admin, Admin $resource): bool
    {
        if (!$admin->can($this->ability(PermissionActionEnum::UPDATE))) {
            return false;
        }

        if (!$resource->new_email_for_verification) {
            throw new TranslatedException(
                __('admins::exceptions.this_admin_has_not_had_their_email_changed_or_has_already_confirmed')
            );
        }

        return true;
    }

    protected function ability(PermissionActionEnum $action): string
    {
        return Ability::toModel(Admin::class)->action($action)->build();
    }
}
