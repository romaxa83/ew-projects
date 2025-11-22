<?php

namespace App\Imports;

use App\Dto\Users\UserDto;
use App\Enums\Permissions\UserRolesEnum;
use App\Exceptions\Users\UserUniqEmailException;
use App\Models\Branches\Branch;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Services\Users\UserService;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithValidation, WithHeadingRow
{
    use Importable;

    public function __construct(private UserService $service)
    {
    }

    public function collection(Collection $collection): void
    {
        $role = Role::whereName(UserRolesEnum::INSPECTOR)
            ->first();
        $language = Language::default()
            ->first();

        foreach ($collection as $user) {
            $dto = UserDto::byArgs(
                [
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'language' => $language->slug,
                    'role_id' => $role->id,
                    'phones' => array_merge(
                        [
                            [
                                'phone' => $user['phone1'],
                                'is_default' => true,
                            ]
                        ],
                        !empty($user['phone2']) ? [
                            [
                                'phone' => $user['phone2'],
                                'is_default' => false,
                            ]
                        ] : [],
                        !empty($user['phone3']) ? [
                            [
                                'phone' => $user['phone3'],
                                'is_default' => false,
                            ]
                        ] : []
                    )
                ]
            );

            try {
                $this->service->create($dto);
            } catch (UserUniqEmailException) {
                continue;
            }
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string'
            ],
            'last_name' => [
                'required',
                'string',
            ],
            'second_name' => [
                'nullable',
                'string',
            ],
            'phone1' => [
                'required',
                'regex:/^380[1-9][0-9]{8}/',
            ],
            'phone2' => [
                'nullable',
                'regex:/^380[1-9][0-9]{8}/',
            ],
            'phone3' => [
                'nullable',
                'regex:/^380[1-9][0-9]{8}/',
            ],
            'email' => [
                'required',
                'email',
            ],
            'branch_id' => [
                'nullable',
                'int',
                Rule::exists(Branch::class, 'id')
            ]
        ];
    }
}
