<?php

namespace App\Http\Requests\Users;

use App\Models\Users\User;
use App\Services\Users\UserService;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class DestroyUserRequest extends FormRequest
{
    use OnlyValidateForm;

    protected function checkRoles(): bool
    {
        /**@var User $user*/
        $user = $this->route()->parameter('user');
        return $user->id !== $this->user()->id && $this->user()->can('roles ' . mb_strtolower($user->getRoleName()));
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->checkRoles() && $this->user()->can('users delete');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [];

        $rules['user'][] = static function ($attribute, User $value, $fail) {
            if ($value->isActive()) {
                $fail(trans('Deactivate user before deleting.'));
            }
        };

        $rules['user'][] = static function ($attribute, User $value, $fail) {
            /**@var UserService $userService*/
            $userService = resolve(UserService::class);

            $role = $value->getRoleName();

            if (in_array($role,  User::DRIVER_ROLES, true) && $userService->driverHasActiveOrders($value)) {
                $fail(trans('This driver has active orders.'));
                return;
            }

            if ($value->isOwner()) {
                if ($value->ownerTrucks()->exists() && $value->ownerTrailers()->exists()) {
                    $fail(trans(
                        'This owner has <a href=":trucks">trucks</a> and <a href=":trailers">trailers</a> assigned.',
                        [
                            'trucks' => str_replace('{id}', $value->id, config('frontend.trucks_with_owner_filter_url')),
                            'trailers' => str_replace('{id}', $value->id, config('frontend.trailers_with_owner_filter_url')),
                        ],
                    ));
                    return;
                }
                if ($value->ownerTrucks()->exists()) {
                    $fail(trans(
                        'This owner has <a href=":trucks">trucks</a> assigned.',
                        [
                            'trucks' => str_replace('{id}', $value->id, config('frontend.trucks_with_owner_filter_url')),
                        ],
                    ));
                    return;
                }
                if ($value->ownerTrailers()->exists()) {
                    $fail(trans(
                        'This owner has <a href=":trailers">trailers</a> assigned.',
                        [
                            'trailers' => str_replace('{id}', $value->id, config('frontend.trailers_with_owner_filter_url')),
                        ],
                    ));
                    return;
                }
            }

            if (in_array($role, [User::SUPERADMIN_ROLE, User::ADMIN_ROLE, User::DISPATCHER_ROLE], true)) {
                if ($userService->managerHasActiveOrders($value)) {
                    $fail(trans('This manager has active orders.'));
                    return;
                }
                if ($userService->managerHasAttachedDrivers($value)) {
                    $fail(trans('This manager has attached drivers.'));
                    return;
                }
            }

            if ($value->isMechanic()) {
                if ($value->hasRelatedOpenBSOrders() || $value->hasRelatedDeletedBSOrders()) {
                    $message = trans('Mechanic is assigned to the ') . '%s.';
                    $assignedIn = [];
                    if ($value->hasRelatedOpenBSOrders()) {
                        $url = str_replace('{id}', $value->id, config('frontend.bs_open_orders_with_mechanic_filter_url'));
                        $assignedIn[] = sprintf('<a href="%s">%s</a>', $url, trans('open orders'));
                    }
                    if ($value->hasRelatedDeletedBSOrders()) {
                        $url = str_replace('{id}', $value->id, config('frontend.bs_deleted_orders_with_mechanic_filter_url'));
                        $assignedIn[] = sprintf('<a href="%s">%s</a>', $url, trans('deleted orders'));
                        $message .= trans(' Please delete order permanently first.');
                    }
                    $fail(sprintf($message, implode(trans(' and '), $assignedIn)));
                    return;
                }
            }
        };

        return $rules;
    }

    public function prepareForValidation(): void
    {
        $this->merge(
            [
                'user' => $this->route()->parameter('user')
            ]
        );
    }
}
