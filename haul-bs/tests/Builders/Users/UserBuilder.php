<?php

namespace Tests\Builders\Users;

use App\Enums\Users\UserStatus;
use App\Foundations\Modules\Permission\Models\Role;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;
use Tests\Builders\BaseBuilder;

class UserBuilder extends BaseBuilder
{
    protected null|Role $role = null;

    function modelClass(): string
    {
        return User::class;
    }

    public function password(string $value): self
    {
        $this->data['password'] = Hash::make($value);
        return $this;
    }

    public function email(string $value): self
    {
        $this->data['email'] = new Email($value);
        return $this;
    }

    public function lang(string $value): self
    {
        $this->data['lang'] = $value;
        return $this;
    }

    public function notVerifyEmail(): self
    {
        $this->data['email_verified_at'] = null;
        return $this;
    }

    public function status(UserStatus $value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    public function deleted(CarbonImmutable|null $value = null): self
    {
        if(!$value) $value = CarbonImmutable::now();

        $this->data['deleted_at'] = $value;
        return $this;
    }

    public function phones(array $value): self
    {
        $this->data['phones'] = $value;
        return $this;
    }

    public function phone(string $value): self
    {
        $this->data['phone'] = new Phone($value);
        return $this;
    }

    public function phoneExt(string $value): self
    {
        $this->data['phone_extension'] = $value;
        return $this;
    }

    public function firstName(string $value): self
    {
        $this->data['first_name'] = $value;
        return $this;
    }

    public function lastName(string $value): self
    {
        $this->data['last_name'] = $value;
        return $this;
    }

    public function asSuperAdmin(): self
    {
        $this->role = Role::superAdmin()->first();

        return $this;
    }

    public function asAdmin(): self
    {
        $this->role = Role::admin()->first();

        return $this;
    }

    public function asMechanic(): self
    {
        $this->role = Role::mechanic()->first();

        return $this;
    }


    public function asSalesManager(): self
    {
        $this->role = Role::salesManager()->first();

        return $this;
    }
    protected function afterSave($model): void
    {
        /** @var $model User */
        if($this->role){
            $model->assignRole($this->role);
        }
    }

    protected function afterClear(): void
    {
        $this->role = null;
    }
}
