<?php

namespace Tests\Builders\Users;

use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use Tests\Builders\BaseBuilder;

class UserBuilder extends BaseBuilder
{
    protected bool $asDriver = false;
    protected bool $asDispatcher = false;
    protected bool $asSuperAdmin = false;
    protected bool $asAdmin = false;

    function modelClass(): string
    {
        return User::class;
    }

    public function asDriver(): self
    {
        $this->asDriver = true;
        return $this;
    }

    public function asSuperAdmin(): self
    {
        $this->asSuperAdmin = true;
        return $this;
    }


    public function asDispatcher(): self
    {
        $this->asDispatcher = true;
        return $this;
    }

    public function asAdmin(): self
    {
        $this->asAdmin = true;
        return $this;
    }

    public function name(string $value): self
    {
        $data = explode(' ', $value);
        $this->data['first_name'] = $data[0];
        $this->data['last_name'] = $data[1] ?? null;

        return $this;
    }

    public function company(Company $model): self
    {
        $this->data['carrier_id'] = $model->id;
        return $this;
    }

    public function email(string $value): self
    {
        $this->data['email'] = $value;
        return $this;
    }

    public function afterSave($model): void
    {
        if($this->asDriver){
            $model->assignRole(User::DRIVER_ROLE);
        }
        if($this->asDispatcher){
            $model->assignRole(User::DISPATCHER_ROLE);
        }
        if($this->asSuperAdmin){
            $model->assignRole(User::SUPERADMIN_ROLE);
        }
        if($this->asAdmin){
            $model->assignRole(User::ADMIN_ROLE);
        }
    }

    public function afterClear(): void
    {
        $this->asDriver = false;
        $this->asDispatcher = false;
        $this->asSuperAdmin = false;
        $this->asAdmin = false;
    }
}

