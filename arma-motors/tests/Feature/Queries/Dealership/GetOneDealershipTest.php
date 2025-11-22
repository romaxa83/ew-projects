<?php

namespace Tests\Feature\Queries\Dealership;

use App\Models\Catalogs\Region\City;
use App\Models\Dealership\Dealership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;

class GetOneDealershipTest extends TestCase
{
    use DatabaseTransactions;
    use Statuses;

    /** @test */
    public function success_by_id()
    {
        $random = Dealership::query()->orderBy(\DB::raw('RAND()'))->first();

        $response = $this->graphQL($this->getQueryStr($random->id));

        $responseData = $response->json('data.dealership');

        $this->assertEquals($random->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('location', $responseData);
        $this->assertArrayHasKey('website', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('text', $responseData['current']);
        $this->assertArrayHasKey('address', $responseData['current']);
        $this->assertArrayHasKey('translations', $responseData);
        $this->assertCount(2, $responseData['translations']);
        $this->assertArrayHasKey('lang', $responseData['translations'][0]);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);

        $this->assertArrayHasKey('departmentSales', $responseData);
        $this->assertArrayHasKey('id', $responseData['departmentSales']);
        $this->assertArrayHasKey('active', $responseData['departmentSales']);
        $this->assertArrayHasKey('sort', $responseData['departmentSales']);
        $this->assertArrayHasKey('phone', $responseData['departmentSales']);
        $this->assertArrayHasKey('email', $responseData['departmentSales']);
        $this->assertArrayHasKey('viber', $responseData['departmentSales']);
        $this->assertArrayHasKey('telegram', $responseData['departmentSales']);
        $this->assertArrayHasKey('type', $responseData['departmentSales']);
        $this->assertEquals($this->department_type_sales, $responseData['departmentSales']['type']);
        $this->assertArrayHasKey('location', $responseData['departmentSales']);
        $this->assertArrayHasKey('current', $responseData['departmentSales']);
        $this->assertArrayHasKey('lang', $responseData['departmentSales']['current']);
        $this->assertArrayHasKey('name', $responseData['departmentSales']['current']);
        $this->assertArrayHasKey('address', $responseData['departmentSales']['current']);

        $this->assertArrayHasKey('departmentService', $responseData);
        $this->assertArrayHasKey('id', $responseData['departmentService']);
        $this->assertArrayHasKey('type', $responseData['departmentService']);
        $this->assertEquals($this->department_type_service, $responseData['departmentService']['type']);

        $this->assertArrayHasKey('departmentCash', $responseData);
        $this->assertArrayHasKey('id', $responseData['departmentCash']);
        $this->assertArrayHasKey('type', $responseData['departmentCash']);
        $this->assertEquals($this->department_type_credit, $responseData['departmentCash']['type']);

        $this->assertArrayHasKey('departmentBody', $responseData);
        $this->assertArrayHasKey('id', $responseData['departmentBody']);
        $this->assertArrayHasKey('type', $responseData['departmentBody']);
        $this->assertEquals($this->department_type_body, $responseData['departmentBody']['type']);

        $this->assertArrayHasKey('images', $responseData);
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            dealership (id: %s) {
                id
                active
                sort
                location
                website
                current {
                    lang
                    name
                    text
                    address
                }
                translations{
                    lang
                }
                brand {
                    id
                    name
                }
                departmentSales {
                    id
                    active
                    sort
                    phone
                    email
                    viber
                    telegram
                    type
                    location
                    current {
                        lang
                        name
                        address
                    }
                    schedule {
                        day
                        from
                        to
                        time
                    }
                }
                departmentService {
                    id
                    type
                    location
                    current {
                        lang
                        name
                        address
                    }
                }
                departmentCash {
                    id
                    type
                    location
                    current {
                        lang
                        name
                        address
                    }
                }
                departmentBody {
                    id
                    type
                    location
                    current {
                        lang
                        name
                        address
                    }
                }
                images {
                    sizes
                }
               }
            }',
            $id
        );
    }
}

