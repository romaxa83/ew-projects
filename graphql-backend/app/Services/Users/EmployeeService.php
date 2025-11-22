<?php

namespace App\Services\Users;

use App\Dto\Users\UserDto;
use App\Exceptions\Employees\ChangeRoleForOwnerException;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use Illuminate\Support\Collection;

class EmployeeService
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    public function create(User $manager, UserDto $userDto): User
    {
        $user = $this->userService->create($userDto);

        CompanyUser::query()->create(
            [
                'user_id' => $user->id,
                'company_id' => $manager->company->id
            ]
        );

        $user->assignRole($userDto->getRoleId());

        return $user;
    }

    public function update(User $user, UserDto $dto): User
    {
        $roleId = $dto->getRoleId();

        if ($user->isOwner()) {
            $this->checkChangeRoleForOwner($user, $roleId);
        }

        $user->syncRoles($roleId);

        return $this->userService->update($user, $dto);
    }

    private function checkChangeRoleForOwner(User $owner, int $roleId): void
    {
        if ($roleId !== $owner->role->id) {
            throw new ChangeRoleForOwnerException(__('exceptions.roles.cant-change-owner-role-owner'));
        }
    }

    public function delete(Collection $users): bool
    {
        $this->userService->delete($users);

        return true;
    }
}
