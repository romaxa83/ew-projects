<?php

namespace Tests\Feature\Queries\Recommendation;

use App\Models\Recommendation\Recommendation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\RecommendationBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;

class RecommendationOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CarBuilder;
    use OrderBuilder;
    use RecommendationBuilder;

    const QUERY = 'recommendation';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $orderUuid = "9ee4670f-1016-11ec-8274-4cd98fc26f15";
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $car = $this->carBuilder()->setUuid($carUuid)->create();
        $order = $this->orderBuilder()->setUuid($orderUuid)->asOne()->create();

        /** @var $model Recommendation */
        $model = $this->recommendationBuilder()
            ->setCarUuid($carUuid)
            ->setOrderUuid($orderUuid)
            ->create();

        $res = $this->graphQL($this->getQueryStr($model->id))
            ->assertOk();

        $this->assertEquals($model->uuid, Arr::get($res, "data.".self::QUERY.".uuid"));
        $this->assertEquals($model->qty, Arr::get($res, "data.".self::QUERY.".qty"));
        $this->assertEquals($model->text, Arr::get($res, "data.".self::QUERY.".text"));
        $this->assertEquals($model->comment, Arr::get($res, "data.".self::QUERY.".comment"));
        $this->assertEquals($model->rejection_reason, Arr::get($res, "data.".self::QUERY.".rejectionReason"));
        $this->assertEquals($model->author, Arr::get($res, "data.".self::QUERY.".author"));
        $this->assertEquals($model->executor, Arr::get($res, "data.".self::QUERY.".executor"));
        $this->assertEquals($model->completed, Arr::get($res, "data.".self::QUERY.".completed"));
        $this->assertEquals($model->completion_at, Arr::get($res, "data.".self::QUERY.".completionAt"));
        $this->assertEquals($model->relevance_at, Arr::get($res, "data.".self::QUERY.".relevanceAt"));

        $this->assertEquals($car->id, Arr::get($res, "data.".self::QUERY.".car.id"));
        $this->assertEquals($model->car_uuid, Arr::get($res, "data.".self::QUERY.".car.uuid"));
        $this->assertEquals($car->uuid, Arr::get($res, "data.".self::QUERY.".car.uuid"));

        $this->assertEquals($order->id, Arr::get($res, "data.".self::QUERY.".order.id"));
        $this->assertEquals($model->order_uuid, Arr::get($res, "data.".self::QUERY.".order.uuid"));
        $this->assertEquals($order->uuid, Arr::get($res, "data.".self::QUERY.".order.uuid"));
    }

    /** @test */
    public function fail_not_model()
    {
        $admin = $this->adminBuilder()
            ->create();
        $this->loginAsAdmin($admin);

        $this->graphQL($this->getQueryStr(1))
            ->assertJson([
                "data" => [
                    self::QUERY => null
                ]
            ])
        ;
    }

    public function getQueryStr(string $id): string
    {
        return  sprintf('{
            %s (id: %s) {
                id
                uuid
                car {
                    id
                    uuid
                }
                order {
                    id
                    uuid
                }
                qty
                text
                comment
                rejectionReason
                author
                executor
                completed
                completionAt
                relevanceAt
                createdAt
                updatedAt
               }
            }',
            self::QUERY,
            $id
        );
    }
}

