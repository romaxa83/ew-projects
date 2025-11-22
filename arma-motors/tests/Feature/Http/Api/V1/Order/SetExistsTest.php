<?php

namespace Tests\Feature\Http\Api\V1\Order;

use App\Exceptions\ErrorsCode;
use App\Models\AA\AAOrder;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\AAPostBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class SetExistsTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;
    use CarBuilder;
    use AAPostBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->armaAuth();
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function success()
    {
        $post = $this->aaPostBuilder()->create();
        $user = $this->userBuilder()->setUuid('4c13fafb-79d6-11ec-8277-4cd98fc26f14')->create();
        $order = $this->orderBuilder()->setUuid('3c13fafb-79d6-11ec-8277-4cd98fc26f14')->asOne()->create();
        $car = $this->carBuilder()->setUuid('5c13fafb-79d6-11ec-8277-4cd98fc26f14')->create();
        $service = Service::find(1);
        $dealership = Dealership::find(1);

        $data = $this->data();
        $data['id'] = $order->uuid->getValue();
        $data['client'] = $user->uuid->getValue();
        $data['auto'] = $car->uuid->getValue();
        $data['type'] = $service->alias;
        $data['base'] = $dealership->alias;
        $data['workshop'] = $post->uuid;
        $data['planning'][0]['workshop'] = $post->uuid;

        $this->assertEmpty(AAOrder::query()->get());

        $response = $this->post(
            route('api.v1.order.set.exist'),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $models = AAOrder::query()->get();

        $this->assertNotEmpty($models);

        /** @var $model AAOrder */
        $model = $models[0];

        $this->assertEquals($model->order_uuid, $data['id']);
        $this->assertEquals($model->user_uuid, $data['client']);
        $this->assertEquals($model->car_uuid, $data['auto']);
        $this->assertEquals($model->service_alias, $data['type']);
        $this->assertEquals($model->sub_service_alias, $data['subtype']);
        $this->assertEquals($model->dealership_alias, $data['base']);
        $this->assertEquals($model->comment, $data['comment']);
        $this->assertTrue($model->is_sys);

        $this->assertEquals($model->post->uuid, $post->uuid);
        $this->assertEquals($model->start_date, Carbon::create($data['startdate']));
        $this->assertEquals($model->end_date, Carbon::create($data['enddate']));

        $this->assertCount(1, $model->planning);
        $this->assertEquals($model->planning[0]->start_date, Carbon::create($data['planning'][0]['startDate']));
        $this->assertEquals($model->planning[0]->end_date, Carbon::create($data['planning'][0]['endDate']));
        $this->assertEquals($model->planning[0]->post_uuid, $data['planning'][0]['workshop']);
    }

    /** @test */
    public function success_but_order_not_system()
    {
        $post = $this->aaPostBuilder()->create();
        $user = $this->userBuilder()->setUuid('4c13fafb-79d6-11ec-8277-4cd98fc26f14')->create();
        $car = $this->carBuilder()->setUuid('5c13fafb-79d6-11ec-8277-4cd98fc26f14')->create();
        $service = Service::find(1);
        $dealership = Dealership::find(1);

        $data = $this->data();
        $data['id'] = '5c13fafb-79d6-11ec-8277-4cd98fc26f14';
        $data['client'] = $user->uuid->getValue();
        $data['auto'] = $car->uuid->getValue();
        $data['type'] = $service->alias;
        $data['base'] = $dealership->alias;
        $data['workshop'] = $post->uuid;
        $data['planning'][0]['workshop'] = $post->uuid;

        $this->assertEmpty(AAOrder::query()->get());

        $response = $this->post(
            route('api.v1.order.set.exist'),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $models = AAOrder::query()->get();

        $this->assertNotEmpty($models);

        /** @var $model AAOrder */
        $model = $models[0];

        $this->assertFalse($model->is_sys);
    }

    /** @test */
    public function success_required_fields()
    {
        $post = $this->aaPostBuilder()->create();

        $data = $this->data();
        $data['workshop'] = $post->uuid;
        $data['planning'][0]['workshop'] = $post->uuid;

        unset(
            $data['id'],
            $data['client'],
            $data['auto'],
            $data['type'],
            $data['subtype'],
            $data['base'],
            $data['comment'],
        );

        $this->assertEmpty(AAOrder::query()->get());

        $response = $this->post(
            route('api.v1.order.set.exist'),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $models = AAOrder::query()->get();

        $this->assertNotEmpty($models);

        /** @var $model AAOrder */
        $model = $models[0];

        $this->assertNull($model->order_uuid);
        $this->assertNull($model->user_uuid);
        $this->assertNull($model->car_uuid);
        $this->assertNull($model->service_alias);
        $this->assertNull($model->sub_service_alias);
        $this->assertNull($model->dealership_alias);
        $this->assertNull($model->comment);

        $this->assertEquals($model->post->uuid, $post->uuid);
        $this->assertEquals($model->start_date, Carbon::create($data['startdate']));
        $this->assertEquals($model->end_date, Carbon::create($data['enddate']));

        $this->assertCount(1, $model->planning);
        $this->assertEquals($model->planning[0]->start_date, Carbon::create($data['planning'][0]['startDate']));
        $this->assertEquals($model->planning[0]->end_date, Carbon::create($data['planning'][0]['endDate']));
        $this->assertEquals($model->planning[0]->post_uuid, $data['planning'][0]['workshop']);
    }

    /** @test */
    public function wrong_auth_token()
    {
        $post = $this->aaPostBuilder()->create();

        $data = $this->data();
        $data['workshop'] = $post->uuid;
        $data['planning'][0]['workshop'] = $post->uuid;

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->post(
            route('api.v1.order.set.exist'),
            $data,
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($response->json('data'), 'Bad authorization token');
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function without_auth_token()
    {
        $post = $this->aaPostBuilder()->create();

        $data = $this->data();
        $data['workshop'] = $post->uuid;
        $data['planning'][0]['workshop'] = $post->uuid;

        $response = $this->post(
            route('api.v1.order.set.exist'),
            $data,
            []
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($response->json('data'), 'Missing authorization header');
        $this->assertFalse($response->json('success'));
    }

    public function data(): array
    {
        return [
            'id' => '3c13fafb-79d6-11ec-8277-4cd98fc26f14',
            'client' => '4c13fafb-79d6-11ec-8277-4cd98fc26f14',
            'auto' => '5c13fafb-79d6-11ec-8277-4cd98fc26f14',
            'type' => 'service',
            'subtype' => null,
            'base' => null,
            'startdate' => '2021-09-01T16:53:40',
            'enddate' => '2021-09-01T17:27:00',
            'workshop' => '6c13fafb-79d6-11ec-8277-4cd98fc26f14',
            'comment' => 'Нова заявка ТЕСТ',
            'planning' => [
                [
                    'startDate' => '2021-09-01T16:53:40',
                    'endDate' => '2021-09-01T17:27:00',
                    'workshop' => '6c13fafb-79d6-11ec-8277-4cd98fc26f14',
                ]
            ]
        ];
    }
}




