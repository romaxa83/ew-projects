<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Models\Catalogs\Service\Service;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GetServiceHaveRealDateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.servicesHaveRealDate');
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('alias', $responseData[0]);

        foreach ($responseData as $k => $item){
            $this->assertEquals($item['alias'], Service::haveRealDate()[$k]);
        }
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            servicesHaveRealDate {
                id
                alias
               }
            }'
        );
    }
}
