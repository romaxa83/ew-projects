<?php

namespace Tests\Feature\Http\Api\V1\Agreement;

use App\Events\Firebase\FcmPush;
use App\Exceptions\ErrorsCode;
use App\Listeners\Firebase\FcmPushListeners;
use App\Models\Agreement\Agreement;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Services\Firebase\FcmAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\Builders\AgreementBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use CarBuilder;
    use UserBuilder;
    use AgreementBuilder;
    use OrderBuilder;
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
        \Event::fake([FcmPush::class]);

        $data = $this->data();
        $user = $this->userBuilder()->setUuid($data["client"])->create();

        $data['phone'] = $user->phone->getValue();
        $car = $this->carBuilder()->setUserId($user->id)->setUuid($data["auto"])->create();

        $dealership = Dealership::find(1);
        $data['base'] = $dealership->alias;

        $orderUuid = '76e6f86a-a9cb-11ec-827c-4cd98fc26f14';
        $order = $this->orderBuilder()->setUuid($orderUuid)->asOne()->create();
        $data['idRequst'] = $orderUuid;


        $this->assertNull(
            Agreement::query()->where('uuid', $data["id"])->first()
        );

        $this->post(
            route('api.v1.agreement.create'),
            $data,
            $this->headers()
        )
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = Agreement::query()->where('uuid', $data["id"])->first();

        $this->assertTrue($model->isNew());

        $this->assertEquals(Arr::get($data, 'client'), $model->user_uuid);
        $this->assertEquals(Arr::get($data, 'auto'), $model->car_uuid);
        $this->assertEquals(Arr::get($data, 'phone'), $model->phone);
        $this->assertEquals(Arr::get($data, 'number'), $model->number);
        $this->assertEquals(Arr::get($data, 'VIN'), $model->vin);
        $this->assertEquals(Arr::get($data, 'author'), $model->author);
        $this->assertEquals(Arr::get($data, 'authorPhone'), $model->author_phone);
        $this->assertEquals(Arr::get($data, 'base'), $model->dealership_alias);
        $this->assertEquals(Arr::get($data, 'idRequst'), $model->base_order_uuid);

        $this->assertCount($model->jobs->count(), Arr::get($data, 'jobs'));
        foreach ($model->jobs as $key => $job){
            $this->assertEquals(Arr::get($data, "jobs.{$key}.name" ), $job->name);
            $this->assertEquals(Arr::get($data, "jobs.{$key}.sum" ), $job->sum);
        }

        $this->assertCount($model->parts->count(), Arr::get($data, 'parts'));
        foreach ($model->parts as $key => $part){
            $this->assertEquals(Arr::get($data, "parts.{$key}.name" ), $part->name);
            $this->assertEquals(Arr::get($data, "parts.{$key}.sum" ), $part->sum);
            $this->assertEquals(Arr::get($data, "parts.{$key}.quantity" ), $part->qty);
        }

        $service = Service::query()
            ->where('alias', Service::SERVICE_ALIAS)
            ->first();

        \Event::assertDispatched(FcmPush::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);

        \Event::assertDispatched(function (FcmPush $event) {
            return $event->action->getAction() == FcmAction::RECONCILIATION_WORK;
        });

        \Event::assertDispatched(function (FcmPush $event) use ($service, $car) {
            return $event->action->getBody() == __('notification.firebase.action_reconciliation_work.body', [
                    'service' => $service->current->name,
                    'number' => $car->number->getValue(),
                    'car' => $car->car_name
                ]);
        });
    }

    /** @test */
    public function success_without_jobs_and_partis()
    {
        $data = $this->data();

        unset(
            $data['jobs'],
            $data['parts'],
            $data['author'],
            $data['authorPhone']
        );

        $user = $this->userBuilder()->setUuid($data["client"])->create();
        $this->carBuilder()->setUserId($user->id)->setUuid($data["auto"])->create();

        $this->assertNull(
            Agreement::query()->where('uuid', $data["id"])->first()
        );

        $this->post(
            route('api.v1.agreement.create'),
            $data,
            $this->headers()
        )
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = Agreement::query()->where('uuid', $data["id"])->first();

        $this->assertEquals(Arr::get($data, 'client'), $model->user_uuid);
        $this->assertEquals(Arr::get($data, 'auto'), $model->car_uuid);
        $this->assertEquals(Arr::get($data, 'phone'), $model->phone);
        $this->assertEquals(Arr::get($data, 'number'), $model->number);
        $this->assertEquals(Arr::get($data, 'VIN'), $model->vin);
        $this->assertNull($model->author);
        $this->assertNull($model->author_phone);
        $this->assertNull($model->dealership_alias);
        $this->assertNull($model->base_order_uuid);

        $this->assertEmpty($model->jobs);
        $this->assertEmpty($model->parts);
    }

    /** @test */
    public function success_edit()
    {
        $dealership_2 = Dealership::find(2);
        /** @var $model Agreement */
        $uuid = '5f6564ad-8f30-11ec-8277-4cd98fc26f14';
        $orderUuidOld = '76e6f86a-a9cb-11ec-827c-4cd98fc26f15';
        $model = $this->agreementBuilder()->setBaseOrderUuid($orderUuidOld)
            ->setUuid($uuid)->setDealershipAlias($dealership_2->alias)->create();

        $data = $this->data();

        $dealership = Dealership::find(1);
        $data['base'] = $dealership->alias;
        $orderUuid = '76e6f86a-a9cb-11ec-827c-4cd98fc26f14';
        $order = $this->orderBuilder()->setUuid($orderUuid)->asOne()->create();
        $data['idRequst'] = $orderUuid;

        $this->assertNotEquals(Arr::get($data, 'client'), $model->user_uuid);
        $this->assertNotEquals(Arr::get($data, 'auto'), $model->car_uuid);
        $this->assertNotEquals(Arr::get($data, 'phone'), $model->phone);
        $this->assertNotEquals(Arr::get($data, 'number'), $model->number);
        $this->assertNotEquals(Arr::get($data, 'VIN'), $model->vin);
        $this->assertNotEquals(Arr::get($data, 'author'), $model->author);
        $this->assertNotEquals(Arr::get($data, 'authorPhone'), $model->author_phone);
        $this->assertNotEquals(Arr::get($data, 'base'), $model->dealership_alias);
        $this->assertNotEquals(Arr::get($data, 'idRequst'), $model->base_order_uuid);

        $this->assertNotEmpty($model->jobs);
        $this->assertNotCount($model->jobs->count(), Arr::get($data, 'jobs'));

        $this->assertNotEmpty($model->parts);
        $this->assertNotCount($model->parts->count(), Arr::get($data, 'parts'));

        $this->post(
            route('api.v1.agreement.create'),
            $data,
            $this->headers()
        )
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertTrue($model->isNew());

        $this->assertEquals(Arr::get($data, 'client'), $model->user_uuid);
        $this->assertEquals(Arr::get($data, 'auto'), $model->car_uuid);
        $this->assertEquals(Arr::get($data, 'phone'), $model->phone);
        $this->assertEquals(Arr::get($data, 'number'), $model->number);
        $this->assertEquals(Arr::get($data, 'VIN'), $model->vin);
        $this->assertEquals(Arr::get($data, 'author'), $model->author);
        $this->assertEquals(Arr::get($data, 'authorPhone'), $model->author_phone);
        $this->assertEquals(Arr::get($data, 'base'), $model->dealership_alias);
        $this->assertEquals(Arr::get($data, 'idRequst'), $model->base_order_uuid);

        $this->assertCount($model->jobs->count(), Arr::get($data, 'jobs'));
        foreach ($model->jobs as $key => $job){
            $this->assertEquals(Arr::get($data, "jobs.{$key}.name" ), $job->name);
            $this->assertEquals(Arr::get($data, "jobs.{$key}.sum" ), $job->sum);
        }

        $this->assertCount($model->parts->count(), Arr::get($data, 'parts'));
        foreach ($model->parts as $key => $part){
            $this->assertEquals(Arr::get($data, "parts.{$key}.name" ), $part->name);
            $this->assertEquals(Arr::get($data, "parts.{$key}.sum" ), $part->sum);
            $this->assertEquals(Arr::get($data, "parts.{$key}.quantity" ), $part->qty);
        }
    }

    /** @test */
    public function success_edit_without_jobs_and_parts_and_author()
    {
        /** @var $model Agreement */
        $uuid = '5f6564ad-8f30-11ec-8277-4cd98fc26f14';
        $model = $this->agreementBuilder()->setUuid($uuid)->create();

        $data = $this->data();

        unset(
            $data['jobs'],
            $data['parts'],
            $data['author'],
            $data['authorPhone'],
        );

        $this->assertNotEmpty($model->jobs);
        $this->assertNotEmpty($model->parts);

        $this->post(
            route('api.v1.agreement.create'),
            $data,
            $this->headers()
        )
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertEmpty($model->jobs);
        $this->assertEmpty($model->parts);
    }

    /** @test */
    public function wrong_auth_token()
    {
        $uuid = '5f6564ad-8f30-11ec-8277-4cd98fc26f14';
        $this->agreementBuilder()->setUuid($uuid)->create();

        $data = $this->data();

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $this->post(
            route('api.v1.agreement.create'),
            $data,
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH)
            ->assertJson([
                "data" => 'Bad authorization token',
                "success" => false,
            ])
        ;
    }

    /** @test */
    public function without_auth_token()
    {
        $uuid = '5f6564ad-8f30-11ec-8277-4cd98fc26f14';
        $this->agreementBuilder()->setUuid($uuid)->create();

        $data = $this->data();

        $headers = $this->headers();
        unset($headers['Authorization']);

        $this->post(
            route('api.v1.agreement.create'),
            $data,
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH)
            ->assertJson([
                "data" => 'Missing authorization header',
                "success" => false,
            ])
        ;
    }

    protected function data(): array
    {
        return [
            "id" => "5f6564ad-8f30-11ec-8277-4cd98fc26f14",
            "client" => "9ee4670f-0016-11ec-8274-4cd98fc26f15",
            "auto" => "8ee4670f-0016-11ec-8274-4cd98fc26f15",
            "phone" => "+380502051123",
            "number" => "AA1071PB",
            "VIN" => "VF1HSRADG582987",
            "author" => "Аудит Софт",
            "authorPhone" => "+789789123123",
            "jobs" => [
                [
                    "name" => "АКБ заміна",
                    "sum" => 156.6,
                ],
                [
                    "name" => "Слюсарні роботи",
                    "sum" => 276,
                ],
                [
                    "name" => "Заміна замка кришки багажника",
                    "sum" => 658.8,
                ],
                [
                    "name" => "Огляд ходової частини",
                    "sum" => 276,
                ],
                [
                    "name" => "Мийка авто (комплекс)",
                    "sum" => 314.97,
                ]
            ],
            "parts" => [
                [
                    "name" => "ПРИВІД ЗАМКА ДВЕРІ",
                    "quantity" => 1,
                    "sum" => 1385.37,
                ],
                [
                    "name" => "Замок багажнику",
                    "quantity" => 1,
                    "sum" => 1487.16,
                ],
                [
                    "name" => "Аккумулятор 70Ah 720А",
                    "quantity" => 1,
                    "sum" => 3216.1,
                ],
            ]
        ];
    }
}

