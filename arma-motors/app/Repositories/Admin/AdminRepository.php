<?php

namespace App\Repositories\Admin;

use App\Models\Admin\Admin;
use App\Repositories\AbstractRepository;
use App\ValueObjects\Email;
use Illuminate\Database\Eloquent\Collection;

class AdminRepository extends AbstractRepository
{
    public function query()
    {
        return Admin::query();
    }

    public function getByEmail(Email $email, $relation = []): ?Admin
    {
        return $this->query()
            ->with($relation)
            ->where('email', $email)
            ->first();
    }

    public function getCountForDashboard(): int
    {
        return $this->query()->notSuperAdmin()->count();
    }

    public function getListForOrder($dealershipId = null, $departmentType = null): Collection
    {
        $query = $this->query()->notSuperAdmin();

        if($dealershipId){
            $query->where('dealership_id', $dealershipId);
        }

        if($departmentType){
            $query->where('department_type', $departmentType);
        }

        return $query->get();
    }
}
