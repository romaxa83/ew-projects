<?php

namespace Tests\Unit\DTO\Dealership;

use App\DTO\Dealership\DealershipDTO;
use App\DTO\Dealership\DealershipTranslationDTO;
use App\DTO\Dealership\DepartmentDTO;
use App\DTO\Dealership\DepartmentTranslationDTO;
use App\DTO\Dealership\ScheduleDTO;
use App\DTO\Dealership\TimeStepDTO;
use App\Exceptions\ErrorsCode;
use App\Services\Dealership\Exception\DealershipException;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Tests\Feature\Mutations\Dealership\DealershipCreateTest;
use Tests\TestCase;

class DealershipDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = DealershipCreateTest::data(1, true, true);

        $dto = DealershipDTO::byArgs($data);

        $this->assertEquals($dto->getWebsite(), $data['website']);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getAlias(), $data['alias']);
        $this->assertTrue($dto->getLocation() instanceof Point);
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation DealershipTranslationDTO */

            $this->assertTrue($translation instanceof DealershipTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$translation->getLang()]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$translation->getLang()]['lang']);
            $this->assertEquals($translation->getText(), $data['translations'][$translation->getLang()]['text']);
            $this->assertEquals($translation->getAddress(), $data['translations'][$translation->getLang()]['address']);
        }
        $this->assertTrue($dto->hasTimeStep());
        foreach ($dto->getTimeStep() as $key => $step){
            /** @var $step TimeStepDTO */
            $this->assertTrue($step instanceof TimeStepDTO);
            $this->assertEquals($step->serviceId, $data['timeStep'][$key]['serviceId']);
            $this->assertEquals($step->step, $data['timeStep'][$key]['step']);
        }
        $this->assertTrue($dto->hasDepartments());
        foreach ($dto->getDepartments() as $key => $department){
            /** @var $department DepartmentDTO */
            $this->assertTrue($department instanceof DepartmentDTO);
            $this->assertTrue($department->getPhone() instanceof Phone);
            $this->assertTrue($department->getEmail() instanceof Email);
            $this->assertNotNull($department->getType());
            $this->assertTrue($dto->getLocation() instanceof Point);
            $this->assertNotEmpty($department->getSchedule());

            foreach ($department->getTranslations() as $k => $translation){

                /** @var $translation DepartmentTranslationDTO */
                $this->assertTrue($translation instanceof DepartmentTranslationDTO);
                $this->assertNotNull($translation->getName());
                $this->assertNotNull($translation->getLang());
                $this->assertNotNull($translation->getAddress());
            }

            foreach ($department->getSchedule() as $schedule){

                /** @var $schedule ScheduleDTO */
                $this->assertTrue($schedule instanceof ScheduleDTO);
                $this->assertNotNull($schedule->getDay());
//                $this->assertNotNull($schedule->getFrom());
//                $this->assertNotNull($schedule->getTo());
            }
        }
    }

    /** @test */
    public function fail_without_departmentSales()
    {
        $data = DealershipCreateTest::data(1, false);

        $this->expectException(DealershipException::class);
        $this->expectExceptionMessage(__('error.dealership.not data for department sales'));
        $this->expectExceptionCode(ErrorsCode::BAD_REQUEST);

        DealershipDTO::byArgs($data);
    }

    /** @test */
    public function fail_without_departmentService()
    {
        $data = DealershipCreateTest::data(1, false);
        $data['departmentSales'] = [];

        $this->expectException(DealershipException::class);
        $this->expectExceptionMessage(__('error.dealership.not data for department service'));
        $this->expectExceptionCode(ErrorsCode::BAD_REQUEST);

        DealershipDTO::byArgs($data);
    }

    /** @test */
    public function fail_without_departmentCredit()
    {
        $data = DealershipCreateTest::data(1, false);
        $data['departmentSales'] = [];
        $data['departmentService'] = [];

        $this->expectException(DealershipException::class);
        $this->expectExceptionMessage(__('error.dealership.not data for department credit'));
        $this->expectExceptionCode(ErrorsCode::BAD_REQUEST);

        DealershipDTO::byArgs($data);
    }

    /** @test */
    public function fail_without_departmentBody()
    {
        $data = DealershipCreateTest::data(1, false);
        $data['departmentSales'] = [];
        $data['departmentService'] = [];
        $data['departmentCash'] = [];

        $this->expectException(DealershipException::class);
        $this->expectExceptionMessage(__('error.dealership.not data for department body'));
        $this->expectExceptionCode(ErrorsCode::BAD_REQUEST);

        DealershipDTO::byArgs($data);
    }

    /** @test */
    public function fill_by_args_without_time_step()
    {
        $data = DealershipCreateTest::data(1);

        $dto = DealershipDTO::byArgs($data);

        $this->assertFalse($dto->hasTimeStep());
        $this->assertEmpty($dto->getTimeStep());
    }

    /** @test */
    public function fill_without_alias()
    {
        $data = DealershipCreateTest::data(1);
        unset($data['alias']);

        $dto = DealershipDTO::byArgs($data);

        $this->assertNull($dto->getAlias());
    }
}

