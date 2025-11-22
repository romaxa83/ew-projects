<?php

namespace App\Services\Users;

use App\Dto\PhoneDto;
use App\Dto\Users\UserDto;
use App\Dto\Users\UserSettingsDto;
use App\Exceptions\Users\UserUniqEmailException;
use App\Exceptions\Utilities\ImportFileIncorrectException;
use App\Exceptions\Utilities\NothingToExportException;
use App\Exports\ImportExample;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\Users\User;
use App\Models\Users\UserBranch;
use App\Notifications\Users\ChangePasswordNotification;
use App\Notifications\Users\SendPasswordNotification;
use App\Traits\HasDownload;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class UserService
{
    use HasDownload;

    public function create(UserDto $dto): User
    {
        return $this->editUser($dto, new User());
    }

    public function update(UserDto $dto, User $user): User
    {
        return $this->editUser($dto, $user);
    }

    private function editUser(UserDto $dto, User $user): User
    {
        $this->checkUniqueUser($dto, $user);

        $user->first_name = $dto->getFirstName();
        $user->last_name = $dto->getLastName();
        $user->second_name = $dto->getSecondName();
        $user->email = $dto->getEmail();
        $user->lang = $dto->getLang();

        $this->setPassword($user);

        if ($user->isDirty()) {
            $user->save();
        }

        $this->setPhones($dto->getPhones(), $user);
        $this->setBranch($user, $dto->getBranchId());
        $user->syncRoles($dto->getRoleId());

        return $user->refresh();
    }

    private function checkUniqueUser(UserDto $dto, User $user): void
    {
        if (User::query()
            ->where('id', '<>', $user->id)
            ->where('email', $dto->getEmail())
            ->exists()
        ) {
            throw new UserUniqEmailException();
        }
    }

    public function setPassword(User $user, bool $regenerate = false): bool
    {
        if ($user->password && $regenerate === false) {
            return false;
        }

        $password = Str::random(8);

        $user->setPassword($password);
        $user->save();

        $user->notify(
            !$regenerate ? new SendPasswordNotification($password) :
                new ChangePasswordNotification($password)
        );

        return true;
    }

    /**
     * @param PhoneDto[] $phones
     * @param User $user
     */
    private function setPhones(array $phones, User $user): void
    {
        $user
            ->phones()
            ->delete();

        $user->phones()
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

    private function setBranch(User $user, ?int $branchId): void
    {
        UserBranch::whereUserId($user->id)
            ->delete();

        if (!$branchId) {
            return;
        }

        UserBranch::insert(
            [
                'user_id' => $user->id,
                'branch_id' => $branchId
            ]
        );
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function show(array $args, array $relation): LengthAwarePaginator
    {
        return User::filter($args)
            ->with($relation)
            ->withCount('inspections')
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }

    public function import(UploadedFile $file): bool
    {
        try {
            Excel::import(new UsersImport($this), $file);
        } catch (ValidationException) {
            throw new ImportFileIncorrectException();
        }
        return true;
    }

    public function export(): array
    {
        $users = User::with(['branch', 'phones'])
            ->withCount('inspections')
            ->get();

        if ($users->isEmpty()) {
            throw new NothingToExportException();
        }

        return [
            'link' => $this->getDownloadXlsxLink(
                $users->toArray(),
                'users',
                UsersExport::class
            )
        ];
    }

    public function importExample(): array
    {
        return [
            'link' => $this->getDownloadXlsxLink(
                [
                    [
                        'first_name',
                        'last_name',
                        'second_name',
                        'phone1',
                        'phone2',
                        'phone3',
                        'email',
                        'branch_id'
                    ]
                ],
                'import_example',
                ImportExample::class
            )
        ];
    }

    public function updateSettings(UserSettingsDto $dto, User $user): User
    {
        $user->authorization_expiration_period = $dto->getAuthorizationExpirationPeriod();
        $user->save();

        return $user->refresh();
    }

    public function uploadAvatar(UploadedFile $file, User $user)
    {
        $user
            ->clearMediaCollection(User::MC_AVATAR)
            ->addMedia($file)
            ->toMediaCollection(User::MC_AVATAR);
    }

    public function deleteAvatar(User $user)
    {
        $user->clearMediaCollection(User::MC_AVATAR);
    }
}
