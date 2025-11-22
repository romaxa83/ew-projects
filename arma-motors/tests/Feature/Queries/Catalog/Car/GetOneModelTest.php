<?php

namespace Tests\Feature\Queries\Catalog\Car;

use App\Models\Catalogs\Car\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\CalcModelBuilder;

class GetOneModelTest extends TestCase
{
    use DatabaseTransactions;
    use CalcModelBuilder;

    /** @test */
    public function success_by_id()
    {
        $calcBuilder = $this->calcModelBuilder();

        $random = Model::query()->orderBy(\DB::raw('RAND()'))->first();

        $calcBuilder->setModelId($random->id)->create();
        $calcBuilder->setModelId($random->id)->create();
        $calcBuilder->setModelId($random->id)->create();
        $calcBuilder->setModelId($random->id)->create();

        $response = $this->graphQL($this->getQueryStr($random->id));

        $responseData = $response->json('data.model');

        $this->assertEquals($random->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('forCalc', $responseData);
        $this->assertArrayHasKey('forCredit', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('image', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);

        $this->assertArrayHasKey('calcs', $responseData);
        $this->assertArrayHasKey('brand', $responseData['calcs'][0]);
        $this->assertArrayHasKey('id', $responseData['calcs'][0]['brand']);
        $this->assertArrayHasKey('spares', $responseData['calcs'][0]);
        $this->assertArrayHasKey('id', $responseData['calcs'][0]['spares'][0]);
        $this->assertArrayHasKey('name', $responseData['calcs'][0]['spares'][0]);

        $this->assertEquals(4, count($responseData['calcs']));

        $this->assertNull($responseData['image']);
        $this->assertEquals($random->name, $responseData['name']);
        $this->assertEquals($random->brand->id, $responseData['brand']['id']);
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            model (id: %s) {
                id
                uuid
                active
                sort
                name
                forCalc
                forCredit
                brand {
                    id
                    name
                }
                image {
                    id
                    sizes
                }
                calcs {
                    brand {
                        id
                    }
                    spares {
                        id
                        name
                    }
                }
               }
            }',
            $id
        );
    }
}
