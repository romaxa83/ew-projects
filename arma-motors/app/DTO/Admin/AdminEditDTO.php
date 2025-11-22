<?php

namespace App\DTO\Admin;

use App\Models\Permission\Role;
use App\Traits\AssetData;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;

class AdminEditDTO
{
    use AssetData;

    private null|string $name;
    private null|string|Role $role;
    private null|Email $email;
    private null|Phone $phone;
    private null|string $lang;
    private null|int $dealershipId;
    private null|int $departmentType;
    private null|int $serviceId;

    private bool $changeName;
    private bool $changeEmail;
    private bool $changePhone;
    private bool $changeRole;
    private bool $changeLang;
    private bool $changeDealershipId;
    private bool $changeDepartmentType;
    private bool $changeServiceId;

    private function __construct(array $data)
    {
        $this->changeName = static::checkFieldExist($data, 'name');
        $this->changeEmail = static::checkFieldExist($data, 'email');
        $this->changePhone = static::checkFieldExist($data, 'phone');
        $this->changeRole = static::checkFieldExist($data, 'roleId');
        $this->changeLang = static::checkFieldExist($data, 'lang');
        $this->changeDealershipId = static::checkFieldExist($data, 'dealershipId');
        $this->changeDepartmentType = static::checkFieldExist($data, 'departmentType');
        $this->changeServiceId = static::checkFieldExist($data, 'serviceId');
    }

    public static function byArgs(
        array $args,
        null|string|Role $role = null
    ): self
    {

        $self = new self($args);

        $self->name = $args['name'] ?? null;
        $self->email =  isset($args['email']) ? new Email($args['email']) : null;
        $self->phone = isset($args['phone']) ? new Phone($args['phone']) : null;
        $self->role = isset($role) ? self::setRole($role) : null;
        $self->lang = $args['lang'] ?? null;
        $self->dealershipId = $args['dealershipId'] ?? null;
        $self->departmentType = $args['departmentType'] ?? null;
        $self->serviceId = $args['serviceId'] ?? null;

        return $self;
    }

    public static function setRole($role)
    {
        if(is_string($role)){
            return $role;
        }

        if($role instanceof Role){
            return $role->name;
        }

        return null;
    }

    public function getName(): null|string
    {
        return $this->name;
    }

    public function getEmail(): null|Email
    {
        return $this->email;
    }

    public function getPhone(): null|Phone
    {
        return $this->phone;
    }

    public function getRole(): null|string
    {
        return $this->role;
    }

    public function getLang(): null|string
    {
        return $this->lang;
    }

    public function getDealershipId(): null|int
    {
        return $this->dealershipId;
    }

    public function getDepartmentType(): null|int
    {
        return $this->departmentType;
    }

    public function getServiceId(): null|int
    {
        return $this->serviceId;
    }

    public function changeName(): bool
    {
        return $this->changeName;
    }

    public function changeEmail(): bool
    {
        return $this->changeEmail;
    }

    public function changePhone(): bool
    {
        return $this->changePhone;
    }

    public function changeRole(): bool
    {
        return $this->changeRole;
    }

    public function changeLang(): bool
    {
        return $this->changeLang;
    }

    public function changeDealershipId(): bool
    {
        return $this->changeDealershipId;
    }

    public function changeDepartmentType(): bool
    {
        return $this->changeDepartmentType;
    }

    public function changeServiceId(): bool
    {
        return $this->changeServiceId;
    }

    public function hasRole(): bool
    {
        return (bool)$this->role;
    }
}
