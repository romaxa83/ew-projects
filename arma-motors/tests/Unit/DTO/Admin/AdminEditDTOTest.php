<?php

namespace Tests\Unit\DTO\Admin;

use App\DTO\Admin\AdminEditDTO;
use App\Models\Admin\Admin;
use App\Models\Dealership\Department;
use App\Models\Permission\Role;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Tests\TestCase;

class AdminEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
            'phone' => '30999922222',
            'roleId' => 1,
            'lang' => 'ru',
            'dealershipId' => 1,
            'departmentType' => Department::TYPE_SALES,
            'serviceId' => 1,
        ];
        $role = 'some_role';

        $dto = AdminEditDTO::byArgs($data, $role);

        $this->assertTrue(is_string($dto->getName()));
        $this->assertTrue(is_string($dto->getRole()));
        $this->assertTrue($dto->getEmail() instanceof Email);
        $this->assertTrue($dto->getPhone() instanceof Phone);
        $this->assertEquals($dto->getEmail(), $data['email']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getPhone(), $data['phone']);
        $this->assertEquals($dto->getRole(), $role);
        $this->assertEquals($dto->getLang(), $data['lang']);
        $this->assertEquals($dto->getDealershipId(), $data['dealershipId']);
        $this->assertEquals($dto->getDepartmentType(), $data['departmentType']);
        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
        $this->assertTrue($dto->changeEmail());
        $this->assertTrue($dto->changeName());
        $this->assertTrue($dto->changePhone());
        $this->assertTrue($dto->changeRole());
        $this->assertTrue($dto->changeLang());
        $this->assertTrue($dto->hasRole());
        $this->assertTrue($dto->changeDealershipId());
        $this->assertTrue($dto->changeDepartmentType());
        $this->assertTrue($dto->changeServiceId());
    }

    /** @test */
    public function check_fill_by_args_role_as_object()
    {
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $data = [
            'roleId' => $role->id,
        ];

        $dto = AdminEditDTO::byArgs($data, $role);

        $this->assertTrue(is_string($dto->getRole()));;
        $this->assertEquals($dto->getRole(), $role->name);
        $this->assertFalse($dto->changeEmail());
        $this->assertFalse($dto->changeName());
        $this->assertFalse($dto->changePhone());
        $this->assertFalse($dto->changeLang());
        $this->assertFalse($dto->changeDealershipId());
        $this->assertFalse($dto->changeDepartmentType());
        $this->assertFalse($dto->changeServiceId());
        $this->assertNull($dto->getDealershipId());
        $this->assertNull($dto->getDepartmentType());
        $this->assertNull($dto->getServiceId());
        $this->assertTrue($dto->changeRole());
        $this->assertTrue($dto->hasRole());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $dto = AdminEditDTO::byArgs([]);

        $this->assertNull($dto->getEmail());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getPhone());
        $this->assertNull($dto->getLang());
        $this->assertNull($dto->getRole());
        $this->assertNull($dto->getDealershipId());
        $this->assertNull($dto->getDepartmentType());
        $this->assertNull($dto->getServiceId());
        $this->assertFalse($dto->changeEmail());
        $this->assertFalse($dto->changeName());
        $this->assertFalse($dto->changePhone());
        $this->assertFalse($dto->changeRole());
        $this->assertFalse($dto->hasRole());
        $this->assertFalse($dto->changeLang());
        $this->assertFalse($dto->changeDealershipId());
        $this->assertFalse($dto->changeDepartmentType());
        $this->assertFalse($dto->changeServiceId());
    }

    /** @test */
    public function check_null_by_args()
    {
        $data = [
            'name' => null,
            'email' => null,
            'phone' => null,
            'roleId' => null,
            'lang' => null,
        ];

        $dto = AdminEditDTO::byArgs($data);

        $this->assertNull($dto->getEmail());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getPhone());
        $this->assertNull($dto->getRole());
        $this->assertNull($dto->getLang());
        $this->assertTrue($dto->changeEmail());
        $this->assertTrue($dto->changeName());
        $this->assertTrue($dto->changePhone());
        $this->assertTrue($dto->changeLang());
        $this->assertTrue($dto->changeRole());
        $this->assertFalse($dto->hasRole());
    }
}

