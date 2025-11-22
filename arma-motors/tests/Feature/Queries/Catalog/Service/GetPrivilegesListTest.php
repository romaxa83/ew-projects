<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Service\DriverAgeToggleActiveTest;
use Tests\Feature\Mutations\Catalog\Service\ToggleActivePrivilegesTest;
use Tests\Feature\Mutations\Catalog\Service\ToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GetPrivilegesListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.privileges');

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

        $count = count($response->json('data.privileges'));

        // запрос на переключение первого элемента
        $this->graphQL(ToggleActivePrivilegesTest::getQueryStr(1));

        $newResponse = $this->graphQL($this->getQueryStrOnlyActive());

        $this->assertNotEquals($count, count($newResponse->json('data.privileges')));
        $this->assertEquals($count -1 , count($newResponse->json('data.privileges')));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            privileges {
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
            privileges (active: true) {
                id
                active
               }
            }'
        );
    }
}

