<?php

namespace App\DTO\Admin;

use App\Models\Permission\Role;
use App\Traits\AssetData;
use App\ValueObjects\Email;
use App\ValueObjects\Password;
use App\ValueObjects\Phone;

class AdminDTO
{
    use AssetData;

    private string $name;
    private Email $email;
    private Password $password;
    private null|string|Role $role;
    private null|Phone $phone;
    private null|int $dealershipId;
    private null|int $departmentType;
    private null|int $serviceId;

    private function __construct()
    {}

    public static function byArgs(array $args, null|string|Role $role = null): self
    {
        static::assetFieldAll($args, 'name');
        static::assetFieldAll($args, 'email');

        $self = new self();

        $self->name = $args['name'];
        $self->email = new Email($args['email']);
        $self->password = new Password;
        $self->phone = isset($args['phone']) ? new Phone($args['phone']) : null;
        $self->role = isset($role) ? self::setRole($role) : null;
        $self->dealershipId = $args['dealershipId'] ?? null;
        $self->departmentType = $args['departmentType'] ?? null;
        $self->serviceId = $args['serviceId'] ?? null;

        return $self;
    }

    public static function empty(): self
    {
        $self = new self();
        $self->password = new Password;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getPhone(): null|Phone
    {
        return $this->phone;
    }

    public function getRole(): null|string
    {
        return $this->role;
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

    public function hasPassword(): bool
    {
        return (bool)$this->password;
    }

    public function hasRole(): bool
    {
        return (bool)$this->role;
    }
}
