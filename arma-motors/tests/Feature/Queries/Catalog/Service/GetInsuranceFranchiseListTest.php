<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Models\Catalogs\Service\Service;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Service\InsuranceFranchiseToggleActiveTest;;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GetInsuranceFranchiseListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.insuranceFranchises');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('active', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]);
        $this->assertNotEmpty($responseData);
    }

    /** @test */
    public function get_active()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_OTHER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOnlyActive());

        $count = count($response->json('data.insuranceFranchises'));

        // запрос на переключение первого элемента
        $this->graphQL(InsuranceFranchiseToggleActiveTest::getQueryStr(1));

        $newResponse = $this->graphQL($this->getQueryStrOnlyActive());

        $this->assertNotEquals($count, count($newResponse->json('data.insuranceFranchises')));
        $this->assertEquals($count -1 , count($newResponse->json('data.insuranceFranchises')));
    }

    /** @test */
    public function get_by_insurance_id()
    {
        $service = Service::where('alias', Service::INSURANCE_ALIAS)
            ->with('childs.insuranceFranchises')->first();

        $this->assertNotEmpty($service->childs[0]->insuranceFranchises);
        $count = $service->childs[0]->insuranceFranchises->count();

        $response = $this->graphQL($this->getQueryStrOnlyInsuranceId($service->childs[0]->id));

        $this->assertNotEmpty($response->json('data.insuranceFranchises'));
        $this->assertCount($count, $response->json('data.insuranceFranchises'));
    }

    /** @test */
    public function get_by_insurance_id_if_not()
    {
        $service = Service::where('alias', Service::BODY_ALIAS)->first();

        $this->assertEmpty($service->insuranceFranchises);

        $response = $this->graphQL($this->getQueryStrOnlyInsuranceId($service->id));

        $this->assertEmpty($response->json('data.insuranceFranchises'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            insuranceFranchises {
                id
                active
                name
                insurances {
                    current {
                        name
                    }
                }
               }
            }'
        );
    }

    public static function getQueryStrOnlyActive(): string
    {
        return  sprintf('{
            insuranceFranchises (active: true) {
                id
                active
               }
            }'
        );
    }

    public static function getQueryStrOnlyInsuranceId($insuranceServiceId): string
    {
        return  sprintf('{
            insuranceFranchises (insuranceId: %d) {
                id
                active
               }
            }',
            $insuranceServiceId
        );
    }
}

