<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use App\Models\Catalogs\Car\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcModelBuilder;

class CalcModelOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcModelBuilder;

    /** @test */
    public function success_by_id_mitsubishi_group()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::query()->where('name', 'mitsubishi')->first();
        $model = $this->calcModelBuilder()
            ->setBrand($brand)
            ->create()
        ;

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.calcModel');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('name', $responseData['brand']);
        $this->assertArrayHasKey('model', $responseData);
        $this->assertArrayHasKey('name', $responseData['model']);
        $this->assertArrayHasKey('mileage', $responseData);
        $this->assertArrayHasKey('value', $responseData['mileage']);
        $this->assertArrayHasKey('engineVolume', $responseData);
        $this->assertArrayHasKey('volume', $responseData['engineVolume']);
        $this->assertArrayHasKey('driveUnit', $responseData);
        $this->assertArrayHasKey('name', $responseData['driveUnit']);
        $this->assertArrayHasKey('transmission', $responseData);
        $this->assertArrayHasKey('current', $responseData['transmission']);
        $this->assertArrayHasKey('name', $responseData['transmission']['current']);
        $this->assertArrayHasKey('works', $responseData);
        $this->assertArrayHasKey('id', $responseData['works'][0]);
        $this->assertArrayHasKey('current', $responseData['works'][0]);
        $this->assertArrayHasKey('name', $responseData['works'][0]['current']);
        $this->assertArrayHasKey('pivot', $responseData['works'][0]);
        $this->assertArrayHasKey('minutes', $responseData['works'][0]['pivot']);
        $this->assertArrayHasKey('id', $responseData['spares'][0]);
        $this->assertArrayHasKey('name', $responseData['spares'][0]);
        $this->assertArrayHasKey('pivot', $responseData['spares'][0]);
        $this->assertArrayHasKey('qty', $responseData['spares'][0]['pivot']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $model = $this->calcModelBuilder()->create();

        $response = $this->graphQL($this->getQueryStr($model->id));

        $response = $this->graphQL($this->getQueryStr(999));

        $this->assertNull($response->json('data.calcModel'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            calcModel (id: %s) {
                id
                brand {
                    name
                }
                model {
                    name
                }
                mileage {
                    value
                }
                engineVolume {
                    volume
                }
                driveUnit {
                    name
                }
                transmission {
                    current {
                        name
                    }
                }
                works {
                    id
                    current {
                        name
                    }
                    pivot {
                        minutes
                    }
                }
                spares {
                    id
                    name
                    pivot {
                        qty
                    }
                }
               }
            }',
            $id
        );
    }
}

