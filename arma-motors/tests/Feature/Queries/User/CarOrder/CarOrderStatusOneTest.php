<?php

namespace Tests\Feature\Queries\User\CarOrder;

use App\Models\User\OrderCar\OrderStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class  CarOrderStatusOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success_by_id()
    {
        $model = OrderStatus::where('id', 1)->first();

        $this->assertNotNull($model);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.carOrderStatus');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('name', $responseData['current']);

        $this->assertEquals($model->current->name, $responseData['current']['name']);
    }

    /** @test */
    public function not_found()
    {
        $response = $this->graphQL($this->getQueryStr(999));
        $this->assertNull($response->json('data.carOrderStatus'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            carOrderStatus (id: %s) {
                id
                sort
                current {
                    name
                }
               }
            }',
            $id
        );
    }
}


