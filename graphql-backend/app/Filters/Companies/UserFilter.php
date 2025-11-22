<?php

namespace App\Filters\Companies;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function query(string $query): void
    {
        $this->where(
            function (Builder $builder) use ($query) {
                $builder
                    ->orWhereRaw("LOWER(users.first_name) LIKE ?", ["%$query%"])
                    ->orWhereRaw("LOWER(users.last_name) LIKE ?", ["%$query%"])
                    ->orWhereRaw("LOWER(users.middle_name) LIKE ?", ["%$query%"])
                    ->orWhereRaw("LOWER(users.email) LIKE ?", ["%$query%"]);
            }
        );
    }

    public function firstName(string $name): void
    {
        $this->where('first_name', 'like', $name);
    }

    public function customNameSort(string $field, string $direction): void
    {
        $userTable = User::TABLE;
        $sortField = "sort_$field";

        $this->selectRaw(
            "CONCAT_WS('',$userTable.last_name,$userTable.first_name,$userTable.middle_name) as $sortField",
        )
            ->getQuery()
            ->orderBy($sortField, $direction);
    }

    public function customCompanySort(string $field, string $direction): void
    {
        $sortField = "sort_$field";
        $usersTable = User::TABLE;
        $companiesTable = Company::TABLE;
        $companyUserTable = CompanyUser::TABLE;

        $this->selectSub(
            CompanyUser::query()
                ->whereColumn("$companyUserTable.user_id", "$usersTable.id")
                ->selectSub(
                    Company::query()
                        ->selectRaw("LOWER($companiesTable.name)")
                        ->whereColumn("$companiesTable.id", "$companyUserTable.company_id")
                        ->limit(1)
                        ->getQuery(),
                    'lower'
                )
                ->limit(1)
                ->getQuery(),
            $sortField
        )->orderBy($sortField, $direction);
    }

    public function lastName(string $name): void
    {
        $this->where('last_name', 'like', $name);
    }

    public function middleName(string $name): void
    {
        $this->where('middle_name', 'like', $name);
    }

    public function email(string $email): void
    {
        $this->where('users.email', 'like', $email);
    }

    public function phone(string $phone): void
    {
        $this->where("LOWER(users.phone) LIKE ?", ["%$phone%"]);
    }

    public function company(int $companyId): void
    {
        $this->whereHas(
            'companyUser',
            function (Builder $query) use ($companyId) {
                return $query->where('company_id', $companyId);
            }
        );
    }

    public function state(string $state): void
    {
        $this->whereHas(
            'companyUser',
            function (Builder $query) use ($state) {
                $query->where('state', $state);
            }
        );
    }

    protected function allowedOrders(): array
    {
        return User::ALLOWED_SORTING_FIELDS;
    }

    protected function allowedOrdersRelations(): array
    {
        return User::ALLOWED_SORTING_FIELDS_RELATIONS;
    }

    protected function orderQuery(string $field, string $direction): void
    {
        if ($field === 'name') {
            $this->orderBy('last_name', $direction)
                ->orderBy('first_name', $direction)
                ->orderBy('middle_name', $direction);

            return;
        }

        $this->orderBy($field, $direction);
    }
}
