<?php

namespace Tests\Unit\DTO\Admin;

use App\DTO\Admin\AdminDTO;
use App\Models\Admin\Admin;
use App\Models\Dealership\Department;
use App\Models\Permission\Role;
use App\ValueObjects\Email;
use App\ValueObjects\Password;
use App\ValueObjects\Phone;
use Tests\TestCase;

class AdminDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
            'phone' => '30999922222',
            'dealershipId' => 1,
            'departmentType' => Department::TYPE_SALES,
            'serviceId' => 1
        ];

        $dto = AdminDTO::byArgs($data);

        $this->assertTrue($dto->getEmail() instanceof Email);
        $this->assertTrue($dto->getPassword() instanceof Password);
        $this->assertTrue($dto->getPhone() instanceof Phone);
        $this->assertEquals($dto->getPassword() , $dto->getPassword());
        $this->assertEquals($dto->getEmail(), $data['email']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getPhone(), $data['phone']);
        $this->assertEquals($dto->getDealershipId(), $data['dealershipId']);
        $this->assertEquals($dto->getDepartmentType(), $data['departmentType']);
        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
    }

    /** @test */
    public function check_fill_by_args_without_phone()
    {
        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
        ];

        $dto = AdminDTO::byArgs($data);

        $this->assertNull($dto->getPhone());
    }

    /** @test */
    public function check_fill_by_args_with_role_as_object()
    {
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
        ];

        $dto = AdminDTO::byArgs($data, $role);

        $this->assertTrue($dto->hasRole());
        $this->assertEquals($dto->getRole(), $role->name);
        $this->assertNull($dto->getDealershipId());
        $this->assertNull($dto->getDepartmentType());
        $this->assertNull($dto->getServiceId());
    }

    /** @test */
    public function check_fill_by_args_with_role_as_string()
    {
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
        ];

        $dto = AdminDTO::byArgs($data, $role->name);

        $this->assertTrue($dto->hasRole());
        $this->assertEquals($dto->getRole(), $role->name);
    }

    /** @test */
    public function fill_without_name()
    {
        $data = [
            'email' => 'test@test.com',
        ];

        $this->expectException(\InvalidArgumentException::class);

        AdminDTO::byArgs($data);
    }

    /** @test */
    public function fill_name_null()
    {
        $data = [
            'email' => 'test@test.com',
            'name' => null,
        ];

        $this->expectException(\InvalidArgumentException::class);

        AdminDTO::byArgs($data);
    }

    /** @test */
    public function fill_without_email()
    {
        $data = [
            'name' => 'test@test.com',
        ];

        $this->expectException(\InvalidArgumentException::class);

        AdminDTO::byArgs($data);
    }

    /** @test */
    public function fill_email_null()
    {
        $data = [
            'name' => 'test@test.com',
            'email' => null,
        ];

        $this->expectException(\InvalidArgumentException::class);

        AdminDTO::byArgs($data);
    }
}
