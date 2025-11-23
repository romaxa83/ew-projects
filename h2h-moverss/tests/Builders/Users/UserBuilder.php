<?php

namespace Tests\Builders\Users;

use App\Models\Division;
use App\Models\User\Role;
use App\User;
use Tests\Builders\BaseBuilder;

class UserBuilder extends BaseBuilder
{
    protected array $roles = [];

    function modelClass(): string
    {
        return User::class;
    }

    public function roles(Role ...$models): self
    {
        foreach ($models as $model) {
            $this->roles[] = $model->id;
        }

        return $this;
    }

    public function division_ids(Division ...$models): self
    {
        $tmp = [];
        foreach ($models as $model) {
            $tmp[] = $model->id;
        }

        $this->data['division_ids'] = $tmp;

        return $this;
    }

    protected function afterSave($model): void
    {
        if(!empty($this->roles)) {
            $tmp = [];
            foreach ($this->roles as $id) {
                $tmp[] = [
                    'role_id' => $id,
                    'user_id' => $model->id,
                ];
            }
            \DB::table('users_roles_2_users')->insert($tmp);
        }
    }

    protected function afterClear(): void
    {
        $this->roles = [];
    }
}
