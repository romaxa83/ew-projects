<?php

namespace Tests\_Helpers;

use App\Models\Admin\Admin;
use App\Models\Admin\Login;
use App\Models\Dealership\Dealership;
use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Carbon\Carbon;
use Database\Factories\Permission\RoleFactory;
use Database\Factories\Permission\RoleTranslationFactory;

class AdminBuilder
{
    private string $email = 'test@admin.com';
    private null|string $name = null;
    private string $password = 'password';
    private string $phone = '+38000990900';
    private int $status = Admin::STATUS_ACTIVE;
    private $role;

    private bool $withLogins = false;
    private null|Carbon $dateLastLogin;

    private bool $withDealership = false;
    private null|Dealership $dealership;
    private $departmentType = null;
    private $serviceId = null;

    private bool $softDeleted = false;

    private $roleName = null;

    private $asSuperAdmin = false;

    public function setRoleName(string $roleName): self
    {
        $this->roleName = $roleName;
        return $this;
    }

    public function getRoleName(): string
    {
        if($this->roleName){
            return $this->roleName;
        }

        return $this->roleName = \Str::random();
    }

    public function setDepartmentType($departmentType): self
    {
        $this->departmentType = $departmentType;
        return $this;
    }

    public function setService($serviceId): self
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getDepartmentType()
    {
        return $this->departmentType;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getEmail()
    {
        return new Email($this->email);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPhone()
    {
        return new Phone($this->phone);
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function attachRole($role)
    {
        $this->role = $role;
        return $this;
    }

    public function softDeleted(): self
    {
        $this->softDeleted = true;

        return $this;
    }

    public function asSuperAdmin(): self
    {
        $this->asSuperAdmin = true;

        return $this;
    }

    // создание роли и привязывание к ней разрешения
    public function createRoleWithPerm(string $perm)
    {
        $this->role = $this->createRole();

        $perm = Permission::query()->where('name', $perm)->first();
        $this->role->givePermissionTo($perm);

        return $this;
    }

    // создание админа с привязаным дц (переданым или рандомным)
    public function withDealership(null|Dealership $dealership = null): self
    {
        $this->withDealership = true;

        if(null == $dealership){
            $dealership = Dealership::query()->orderBy(\DB::raw('RAND()'))->first();
        }

        $this->dealership = $dealership;

        return $this;
    }

    // создание записей по последним логинам в сиситеме
    public function withLastLogins(?Carbon $date = null)
    {
        $this->withLogins = true;

        $this->dateLastLogin = $date;

        return $this;
    }

    public function createRoleWithPerms(array $perms)
    {
        $this->role = $this->createRole();
        $this->role->syncPermissions($perms);

        return $this;
    }

    public function create()
    {
        $admin = $this->save();

        if ($this->role){
            $admin->assignRole($this->role);
        }

        //@todo порефакторить
        if($this->withLogins){
            if($this->dateLastLogin){

                Login::factory()->new([
                    'admin_id' => $admin->id,
                    'created_at' => $this->dateLastLogin->subHour(random_int(3,6)),
                ])->count(3)->create();


                Login::factory()->new([
                    'admin_id' => $admin->id,
                    'created_at' => $this->dateLastLogin,
                ])->create();
            } else {
                Login::factory()->new([
                    'admin_id' => $admin->id,
                    'created_at' => Carbon::now()->subHour(random_int(3,6)),
                ])->count(3)->create();
            }
        }

        $this->clear();

        return $admin;
    }

    private function save()
    {
        $data = [
            'email' => $this->getEmail(),
            'password' => \Hash::make($this->password),
            'status' => $this->getStatus(),
            'phone' => $this->getPhone(),
            'name' => null == $this->getName() ? "name_" . rand(1,100) : $this->getName(),
            'department_type' => $this->getDepartmentType(),
            'service_id' => $this->getServiceId(),
        ];

        if($this->asSuperAdmin){
            $data['name'] = config('permission.roles.super_admin');
        }

        if($this->withDealership){
            $data['dealership_id'] = $this->dealership->id;
        }
        if($this->softDeleted){
            $data['deleted_at'] = Carbon::now();
        }

        return Admin::factory()->new($data)->create();
    }

    public function createRole(): Role
    {
        $role = RoleFactory::new([
            'guard_name' => Admin::GUARD,
            'name' => $this->getRoleName()
        ])->create();

        RoleTranslationFactory::new([
            'role_id' => $role->id,
            'name' => $this->getRoleName()
        ])->create(['lang' => 'ru']);
        RoleTranslationFactory::new([
            'role_id' => $role->id,
            'name' => $this->getRoleName()
        ])->create(['lang' => 'uk']);

        return $role;
    }

    private function clear()
    {
        $this->email = 'test@admin.com';
        $this->name = null;
        $this->password = 'password';
        $this->phone = '+38000990900';
        $this->status = Admin::STATUS_ACTIVE;
        $this->role = null;
        $this->withLogins = false;
        $this->dateLastLogin = null;
        $this->withDealership = false;
        $this->dealership = null;
        $this->softDeleted = false;
        $this->roleName = null;
        $this->asSuperAdmin = false;
        $this->serviceId = null;
    }
}
