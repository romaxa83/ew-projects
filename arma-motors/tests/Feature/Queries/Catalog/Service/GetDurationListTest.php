<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Models\Catalogs\Service\Service;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Service\DurationToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GetDurationListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.durations');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('active', $responseData[0]);
        $this->assertArrayHasKey('current', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]['current']);
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

        $count = count($response->json('data.durations'));

        // запрос на переключение первого элемента
        $this->graphQL(DurationToggleActiveTest::getQueryStr(1));

        $newResponse = $this->graphQL($this->getQueryStrOnlyActive());

        $this->assertNotEquals($count, count($newResponse->json('data.durations')));
        $this->assertEquals($count -1 , count($newResponse->json('data.durations')));
    }

    /** @test */
    public function get_by_service_id()
    {
        $service = Service::where('alias', Service::INSURANCE_ALIAS)
            ->with('childs.durations')->first();

        $this->assertNotEmpty($service->durations);
        $count = $service->durations->count();

        $response = $this->graphQL($this->getQueryStrOnlyServiceId($service->id));

        $this->assertNotEmpty($response->json('data.durations'));
        $this->assertCount($count, $response->json('data.durations'));
    }

    /** @test */
    public function get_by_service_id_if_not()
    {
        $service = Service::where('alias', Service::SERVICE_ALIAS)
            ->with('childs.durations')->first();

        $this->assertEmpty($service->durations);

        $response = $this->graphQL($this->getQueryStrOnlyServiceId($service->id));

        $this->assertEmpty($response->json('data.durations'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            durations {
                id
                active
                current {
                    name
                }
               }
            }'
        );
    }

    public static function getQueryStrOnlyActive(): string
    {
        return  sprintf('{
            durations (active: true) {
                id
                active
               }
            }'
        );
    }

    public static function getQueryStrOnlyServiceId($serviceId): string
    {
        return  sprintf('{
            durations (serviceId: %d) {
                id
                active
               }
            }',
            $serviceId
        );
    }
}

