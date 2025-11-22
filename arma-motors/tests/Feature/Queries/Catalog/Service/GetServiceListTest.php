<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Models\Catalogs\Service\Service;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Service\ToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GetServiceListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.services');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('current', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]['current']);
        $this->assertArrayHasKey('childs', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['childs'][0]);
        $this->assertArrayHasKey('current', $responseData[0]['childs'][0]);
        $this->assertArrayHasKey('name', $responseData[0]['childs'][0]['current']);
        $this->assertArrayHasKey('parent', $responseData[0]['childs'][0]);
        $this->assertArrayHasKey('icon', $responseData[0]);
        $this->assertArrayHasKey('forGuest', $responseData[0]);

        $services = Service::all();
        foreach ($services as $service){
            if($service->isService()){
                $this->assertTrue($service->for_guest);
            } else {
                $this->assertFalse($service->for_guest);
            }
        }
    }

    /** @test */
    public function get_active()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOnlyActive());

        $count = count($response->json('data.services'));

        // запрос на переключение первого элемента
        $this->graphQL(ToggleActiveTest::getQueryStr(1));

        $newResponse = $this->graphQL($this->getQueryStrOnlyActive());

        $this->assertNotEquals($count, count($newResponse->json('data.services')));
        $this->assertEquals($count -1 , count($newResponse->json('data.services')));
    }

    public static function getQueryStrOnlyActive(): string
    {
        return  sprintf('{
            services (active: true) {
                id
                active
               }
            }'
        );
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            services {
                id
                alias
                icon
                forGuest
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
            }'
        );
    }
}
