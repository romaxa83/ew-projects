<?php

namespace Tests\Unit\Models\Catalog\Service;

use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Department;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    /** @test */
    public function isService()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::SERVICE_ALIAS)->first();

        $this->assertTrue($model->isService());
        $this->assertTrue($model->isRelateToAA());
        $this->assertFalse($model->isRelateToSystem());
        $this->assertFalse($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_SERVICE);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_SERVICE);
    }

    /** @test */
    public function isTo()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::SERVICE_TO_ALIAS)->first();

        $this->assertTrue($model->isTo());
        $this->assertTrue($model->isRelateToAA());
        $this->assertFalse($model->isRelateToSystem());
        $this->assertTrue($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_SERVICE);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_SERVICE);
    }

    /** @test */
    public function isDiagnostic()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::SERVICE_DIAGNOSTIC_ALIAS)->first();

        $this->assertTrue($model->isDiagnostic());
        $this->assertTrue($model->isRelateToAA());
        $this->assertFalse($model->isRelateToSystem());
        $this->assertTrue($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_SERVICE);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_SERVICE);
    }

    /** @test */
    public function isTire()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::SERVICE_TIRE_ALIAS)->first();

        $this->assertTrue($model->isTire());
        $this->assertTrue($model->isRelateToAA());
        $this->assertFalse($model->isRelateToSystem());
        $this->assertTrue($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_SERVICE);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_SERVICE);
    }

    /** @test */
    public function isOther()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::SERVICE_OTHER_ALIAS)->first();

        $this->assertTrue($model->isOther());
        $this->assertTrue($model->isRelateToAA());
        $this->assertFalse($model->isRelateToSystem());
        $this->assertTrue($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_SERVICE);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_SERVICE);
    }

    /** @test */
    public function isBody()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::BODY_ALIAS)->first();

        $this->assertTrue($model->isBody());
        $this->assertTrue($model->isRelateToAA());
        $this->assertFalse($model->isRelateToSystem());
        $this->assertFalse($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_BODY);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_BODY);
    }

    /** @test */
    public function isInsurance()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::INSURANCE_ALIAS)->first();

        $this->assertTrue($model->isInsurance());
        $this->assertFalse($model->isRelateToAA());
        $this->assertTrue($model->isRelateToSystem());
        $this->assertFalse($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_CASH);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_CREDIT);
    }

    /** @test */
    public function isCasco()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::INSURANCE_CASCO_ALIAS)->first();

        $this->assertTrue($model->isCasco());
        $this->assertFalse($model->isRelateToAA());
        $this->assertTrue($model->isRelateToSystem());
        $this->assertFalse($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_CASH);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_CREDIT);
    }

    /** @test */
    public function isGo()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::INSURANCE_GO_ALIAS)->first();

        $this->assertTrue($model->isGo());
        $this->assertFalse($model->isRelateToAA());
        $this->assertTrue($model->isRelateToSystem());
        $this->assertFalse($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_CASH);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_CREDIT);
    }

    /** @test */
    public function isCredit()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::CREDIT_ALIAS)->first();

        $this->assertTrue($model->isCredit());
        $this->assertFalse($model->isRelateToAA());
        $this->assertTrue($model->isRelateToSystem());
        $this->assertFalse($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_CASH);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_CREDIT);
    }

    /** @test */
    public function isSpares()
    {
        /** @var $model Service */
        $model = Service::whereAlias(Service::SPARES_ALIAS)->first();

        $this->assertTrue($model->isSpares());
        $this->assertTrue($model->isRelateToAA());
        $this->assertFalse($model->isRelateToSystem());
        $this->assertFalse($model->isServiceParent());
        $this->assertEquals($model->getOrderDepartment(), Department::DEPARTMENT_SERVICE);
        $this->assertEquals($model->getOrderDepartment(true), Department::TYPE_SERVICE);
    }

    /** @test */
    public function have_real_date()
    {
        /** @var $model Service */
        $data = Service::haveRealDate();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertEquals($data[0], Service::BODY_ALIAS);
        $this->assertEquals($data[1], Service::SERVICE_DIAGNOSTIC_ALIAS);
        $this->assertEquals($data[2], Service::SERVICE_OTHER_ALIAS);
        $this->assertEquals($data[3], Service::SERVICE_TIRE_ALIAS);
        $this->assertEquals($data[4], Service::SERVICE_TO_ALIAS);
    }

}




