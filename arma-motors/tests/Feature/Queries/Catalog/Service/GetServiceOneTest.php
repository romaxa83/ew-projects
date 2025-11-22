<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetServiceOneTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success_by_id()
    {
        $service = Service::where('id', 1)->first();

        $response = $this->graphQL($this->getQueryStr($service->id));

        $responseData = $response->json('data.service');

        $this->assertEquals($service->id, $responseData['id']);
        $this->assertEquals($service->alias, $responseData['alias']);
        $this->assertEquals($service->time_step, $responseData['timeStep']);
        $this->assertEquals(count($service->childs), count($responseData['childs']));
    }

    /** @test */
    public function success_by_alias()
    {
        $service = Service::where('id', 1)->first();

        $response = $this->graphQL($this->getQueryStrByAlias($service->alias));

        $responseData = $response->json('data.service');

        $this->assertEquals($service->id, $responseData['id']);
        $this->assertEquals($service->alias, $responseData['alias']);
        $this->assertEquals(count($service->childs), count($responseData['childs']));

        $this->assertArrayHasKey('insuranceCompany', $responseData);
        $this->assertArrayHasKey('orderDepartment', $responseData);
        $this->assertEmpty($responseData['insuranceCompany']);
        $this->assertArrayHasKey('countPayments', $responseData);
        $this->assertEmpty($responseData['countPayments']);
    }

    /** @test */
    public function get_insurances_and_franchise_data()
    {
        $response = $this->graphQL($this->getQueryStrByAlias(Service::INSURANCE_ALIAS));

        $responseData = $response->json('data.service');

        $this->assertNotEmpty($responseData['childs']);
        $this->assertArrayHasKey('insuranceFranchises', $responseData['childs'][0]);
        $this->assertNotEmpty($responseData['childs'][0]['insuranceFranchises']);
        $this->assertArrayHasKey('id', $responseData['childs'][0]['insuranceFranchises'][0]);
        $this->assertArrayHasKey('name', $responseData['childs'][0]['insuranceFranchises'][0]);
        $this->assertArrayHasKey('active', $responseData['childs'][0]['insuranceFranchises'][0]);


        $this->assertArrayHasKey('orderDepartment', $responseData);
        $this->assertEquals(Department::DEPARTMENT_CASH, $responseData['orderDepartment']);
    }

    /** @test */
    public function get_credit_and_duration_data()
    {
        $response = $this->graphQL($this->getQueryStrByAlias(Service::CREDIT_ALIAS));

        $responseData = $response->json('data.service');

        $this->assertEquals($responseData['alias'], Service::CREDIT_ALIAS);
        $this->assertNotEmpty($responseData['durations']);
        $this->assertArrayHasKey('id', $responseData['durations'][0]);
        $this->assertArrayHasKey('current', $responseData['durations'][0]);
        $this->assertArrayHasKey('name', $responseData['durations'][0]['current']);

        $this->assertArrayHasKey('insuranceCompany', $responseData);
        $this->assertEmpty($responseData['insuranceCompany']);
        $this->assertArrayHasKey('countPayments', $responseData);
        $this->assertEmpty($responseData['countPayments']);
    }

    /** @test */
    public function get_insurance_and_duration_data()
    {
        $response = $this->graphQL($this->getQueryStrByAlias(Service::INSURANCE_ALIAS));

        $responseData = $response->json('data.service');

        $this->assertEquals($responseData['alias'], Service::INSURANCE_ALIAS);
        $this->assertNotEmpty($responseData['durations']);
        $this->assertArrayHasKey('id', $responseData['durations'][0]);
        $this->assertArrayHasKey('current', $responseData['durations'][0]);
        $this->assertArrayHasKey('name', $responseData['durations'][0]['current']);

        $this->assertArrayHasKey('insuranceCompany', $responseData);
        $this->assertEmpty($responseData['insuranceCompany']);
        $this->assertArrayHasKey('countPayments', $responseData);
        $this->assertEmpty($responseData['countPayments']);
    }

    /** @test */
    public function get_casco()
    {
        $response = $this->graphQL($this->getQueryStrByAlias(Service::INSURANCE_CASCO_ALIAS));

        $responseData = $response->json('data.service');

        $this->assertEquals($responseData['alias'], Service::INSURANCE_CASCO_ALIAS);
        $this->assertArrayHasKey('insuranceCompany', $responseData);
        $this->assertNotEmpty($responseData['insuranceCompany']);
        $this->assertArrayHasKey('countPayments', $responseData);
        $this->assertNotEmpty($responseData['countPayments']);
    }

    /** @test */
    public function no_by_id()
    {
        $response = $this->graphQL($this->getQueryStr(200));

        $this->assertNull($response->json('data.service'));
    }

    public static function getQueryStr($id): string
    {
        return  sprintf('{
            service (id: "%s") {
                id
                alias
                timeStep
                current {
                    name
                }
                childs {
                    id
                    alias
                    current {
                        name
                    }
                    parent {
                        id
                        current {
                            name
                        }
                    }
                }
               }
            }',
            $id
        );
    }

    public static function getQueryStrByAlias(string $alias): string
    {
        return  sprintf('{
            service (alias: "%s") {
                id
                alias
                orderDepartment
                current {
                    name
                }
                childs {
                    id
                    alias
                    current {
                        name
                    }
                    parent {
                        id
                        current {
                            name
                        }
                    }
                    insuranceFranchises {
                        id
                        active
                        name
                    }
                }
                durations {
                    id
                    current {
                        name
                    }
                }
                insuranceCompany
                countPayments
               }
            }',
            $alias
        );
    }
}

