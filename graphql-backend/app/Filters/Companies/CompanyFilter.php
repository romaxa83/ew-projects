<?php

namespace App\Filters\Companies;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @mixin Company
 */
class CompanyFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function name(string $name): void
    {
        $name = Str::lower($name);

        $this->where(function (Builder $builder) use ($name) {
            $builder->where(
                function (Builder $q) use ($name) {
                    $q->orWhereRaw("LOWER(name) LIKE ?", ["%$name%"]);
                }
            )->orWhereHas('users', function (Builder $q) use ($name) {
                $q->where('state', Company::STATE_OWNER);

                $q->where(function (Builder $q) use ($name) {
                    $q->where(function (Builder $q) use ($name) {
                        $q->orWhereRaw(
                            "LOWER(CONCAT_WS(' ', first_name, last_name, middle_name)) LIKE ?",
                            ["%$name%"]
                        );
                    })->orWhereRaw("LOWER(email) LIKE ?", ["%$name%"]);
                });
            });
        });
    }

    public function ids(array $ids = []): void
    {
        $this->when(count($ids), function (Builder $query) use ($ids) {
            $query->whereKey($ids);
        });
    }

    public function query(string $query): void
    {
        $tableName = Company::TABLE;

        $this->where(
            function (Builder $builder) use ($query, $tableName) {
                $lowerCaseWord = Str::lower($query);

                $builder
                    ->orWhereRaw("LOWER($tableName.name) LIKE ?", ["%$lowerCaseWord%"]);
            }
        );
    }

    public function onlyNew(): void
    {
        $this->underConsideration();
    }

    protected function allowedOrders(): array
    {
        return Company::ALLOWED_SORTING_FIELDS;
    }

    protected function customNameSort(string $field, string $direction): void
    {
        $companyTable = Company::TABLE;
        $companyUserTable = CompanyUser::TABLE;
        $userTable = User::TABLE;

        $this->selectSub(
            User::query()
                ->selectRaw(
                    "
                    case when $companyTable.name IS NULL or $companyTable.name = ''
                    then
                        CONCAT_WS('',$userTable.first_name,$userTable.last_name,$userTable.middle_name)
                    else
                        $companyTable.name
                    end as $field
                "
                )
                ->join($companyUserTable, "$companyUserTable.user_id", '=', "$userTable.id")
                ->whereRaw("$companyUserTable.company_id = $companyTable.id")
                ->whereRaw("$companyUserTable.state = '" . Company::STATE_OWNER . "'")
                ->limit(1)
                ->getQuery(),
            $field
        )->orderBy($field, $direction);
    }

    protected function customUsersSort(string $field, string $direction): void
    {
        $this->selectSub(
            CompanyUser::query()
                ->selectRaw('count(' . CompanyUser::TABLE . '.user_id)')
                ->whereRaw(CompanyUser::TABLE . '.company_id = ' . Company::TABLE . '.id')
                ->groupByRaw(CompanyUser::TABLE . '.company_id')
                ->getQuery(),
            $field . '_sort'
        )->orderBy($field . '_sort', $direction);
    }

    protected function customEmailSort(string $field, string $direction): void
    {
        $this->selectSub(
            User::query()->select($field)
                ->join(CompanyUser::TABLE, CompanyUser::TABLE . '.user_id', '=', User::TABLE . '.id')
                ->whereRaw(CompanyUser::TABLE . '.company_id = ' . Company::TABLE . '.id')
                ->limit(1)
                ->getQuery(),
            $field
        )->orderBy($field, $direction);
    }
}
