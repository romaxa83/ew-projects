<?php

namespace Tests\Feature\Queries\Catalog\Car;

use App\Models\Catalogs\Car\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetOneBrandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success_by_id()
    {
        $random = Brand::query()->orderBy(\DB::raw('RAND()'))->first();

        $response = $this->graphQL($this->getQueryStr($random->id));

        $responseData = $response->json('data.brand');

        $this->assertEquals($random->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertArrayHasKey('isMain', $responseData);
        $this->assertArrayHasKey('color', $responseData);
        $this->assertArrayHasKey('models', $responseData);
        $this->assertArrayHasKey('id', $responseData['models'][0]);
        $this->assertArrayHasKey('name', $responseData['models'][0]);
        $this->assertArrayHasKey('image', $responseData);
        $this->assertArrayHasKey('hourlyPayment', $responseData);
        $this->assertArrayHasKey('discountHourlyPayment', $responseData);
        $this->assertArrayHasKey('works', $responseData);
        $this->assertArrayHasKey('mileages', $responseData);

        $this->assertEquals($random->name, $responseData['name']);
        $this->assertNull($responseData['image']);
        $this->assertNull($responseData['discountHourlyPayment']);
        $this->assertIsArray($responseData['works']);
        $this->assertEmpty($responseData['works']);
        $this->assertIsArray($responseData['mileages']);
        $this->assertEmpty($responseData['mileages']);
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            brand (id: %s) {
                id
                uuid
                active
                sort
                isMain
                name
                color
                models {
                    id
                    name
                }
                image {
                    id
                    sizes
                }
                hourlyPayment
                discountHourlyPayment
                works {
                    id
                }
                mileages {
                    id
                }
               }
            }',
            $id
        );
    }
}
