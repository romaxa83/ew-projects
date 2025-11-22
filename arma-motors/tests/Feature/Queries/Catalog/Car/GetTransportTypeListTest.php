<?php

namespace Tests\Feature\Queries\Catalog\Car;

use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Car\TransportTypeToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GetTransportTypeListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.transportTypes');

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

        $count = count($response->json('data.transportTypes'));

        // запрос на переключение первого элемента
        $this->graphQL(TransportTypeToggleActiveTest::getQueryStr(1));

        $newResponse = $this->graphQL($this->getQueryStrOnlyActive());

        $this->assertNotEquals($count, count($newResponse->json('data.transportTypes')));
        $this->assertEquals($count -1 , count($newResponse->json('data.transportTypes')));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            transportTypes {
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
            transportTypes (active: true) {
                id
                active
               }
            }'
        );
    }
}

