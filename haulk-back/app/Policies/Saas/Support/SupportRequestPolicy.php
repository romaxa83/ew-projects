<?php

namespace App\Policies\Saas\Support;

use App\Http\Resources\Saas\Support\Crm\SupportResource as CrmSupportResource;
use App\Http\Resources\Saas\Support\Backoffice\SupportResource as BackOfficeSupportResource;
use App\Models\Admins\Admin;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Users\User;
use App\Permissions\Saas\Support\SupportRequestShow;
use App\Permissions\Saas\Support\SupportRequestUpdate;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  User|Admin  $user
     * @param  SupportRequest  $supportRequest
     * @return bool
     */
    /*public function update($user, SupportRequest $supportRequest): bool
    {
        if ($user->can('support-requests update') === false && $user->can(SupportRequestUpdate::KEY) === false) {
            return false;
        }

        return $supportRequest->user_id !== null
            && !in_array(
                $supportRequest->status,
                [
                    SupportRequest::STATUS_CLOSED
                ],
                true
            )
            && (
                ($user instanceof User && $user->id === $supportRequest->user_id)
                ||
                ($user instanceof Admin && $user->id === $supportRequest->admin_id)
            );
    }*/

    public function setLabel(Admin $admin, SupportRequest $supportRequest): bool
    {
        if ($admin->can(SupportRequestUpdate::KEY) === false) {
            return false;
        }

        if (in_array($supportRequest->status, [SupportRequest::STATUS_CLOSED], true)) {
            return false;
        }

        return true;
    }

    /**
     * @param Admin $admin
     * @param SupportRequest|BackOfficeSupportResource $supportRequest
     * @return bool
     */
    public function answer(Admin $admin, $supportRequest): bool
    {
        if ($admin->can(SupportRequestUpdate::KEY) === false) {
            return false;
        }

        return $supportRequest->admin_id === $admin->id && $supportRequest->status === SupportRequest::STATUS_IN_WORK && $supportRequest->user_id !== null;
    }

    /**
     * @param Admin $admin
     * @param SupportRequest|BackOfficeSupportResource $supportRequest
     * @return bool
     */
    public function take(Admin $admin, $supportRequest): bool
    {
        if ($admin->can(SupportRequestUpdate::KEY) === false) {
            return false;
        }
        return $supportRequest->status === SupportRequest::STATUS_NEW && $supportRequest->admin_id === null;
    }

    /**
     * @param Admin $admin
     * @param SupportRequest|BackOfficeSupportResource $supportRequest
     * @return bool
     */
    public function backofficeClose(Admin $admin, $supportRequest): bool
    {
        if ($admin->can(SupportRequestUpdate::KEY) === false) {
            return false;
        }

        return $supportRequest->status === SupportRequest::STATUS_IN_WORK;
    }

    public function read($user, SupportRequest $supportRequest): bool
    {
        return $user->can('support-requests read') || $user->can(SupportRequestShow::KEY);
    }

    /**
     * @param User $user
     * @param SupportRequest|CrmSupportResource $supportRequest
     * @return bool
     */
    public function crmClose(User $user, $supportRequest): bool
    {
        if (!in_array($supportRequest->status, [SupportRequest::STATUS_NEW, SupportRequest::STATUS_IN_WORK], true)) {
            return false;
        }

        return $user->can('support-requests close')
            || ($user->can('support-requests close-own') && $user->id === $supportRequest->user_id);
    }

    /**
     * @param User $user
     * @param SupportRequest|CrmSupportResource $supportRequest
     * @return bool
     */
    public function reply(User $user, $supportRequest): bool
    {
        return $user->can('support-requests reply')
            || ($user->can('support-requests reply-own') && $user->id === $supportRequest->user_id);
    }
}
