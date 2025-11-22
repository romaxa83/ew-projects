<?php

namespace Tests\Feature\Http\Api\V1\Recommendation;

use App\Events\Firebase\FcmPush;
use App\Exceptions\ErrorsCode;
use App\Listeners\Firebase\FcmPushListeners;
use App\Models\Catalogs\Service\Service;
use App\Models\Recommendation\Recommendation;
use App\Services\Firebase\FcmAction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\RecommendationBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use CarBuilder;
    use OrderBuilder;
    use UserBuilder;
    use RecommendationBuilder;
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
        \Event::fake([
            FcmPush::class
        ]);
        $service = Service::find(1);
        $data = $this->data();
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->setUuid($data["auto"])->create();
        $this->orderBuilder()->setServiceId($service->id)->setUuid($data["request"])->create();

        $model = Recommendation::query()->where('uuid', $data["uuid"])->first();
        $this->assertNull($model);

        $res = $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($res->json('data'));
        $this->assertTrue($res->json('success'));

        $model = Recommendation::query()->where('uuid', $data["uuid"])->first();
        $this->assertNotNull($model);
        $this->assertEquals($model->car_uuid, $data["auto"]);
        $this->assertEquals($model->order_uuid, $data["request"]);
        $this->assertEquals($model->text, $data["recommendation"]);
        $this->assertEquals($model->comment, $data["comment"]);
        $this->assertEquals($model->qty, $data["quantity"]);
        $this->assertEquals($model->rejection_reason, $data["rejectionReason"]);
        $this->assertEquals($model->author, $data["author"]);
        $this->assertEquals($model->executor, $data["executor"]);
        $this->assertEquals($model->completed, $data["completed"]);
        $this->assertEquals($model->completion_at->timestamp, $data["dateCompletion"]);
        $this->assertEquals($model->relevance_at->timestamp, $data["dateRelevance"]);
        $this->assertEquals($model->status, Recommendation::STATUS_NEW);
        $this->assertNotNull($model->data);

        $this->assertEquals($model->user->id, $user->id);
        $this->assertTrue($model->isNew());

        \Event::assertDispatched(FcmPush::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);

        \Event::assertDispatched(function (FcmPush $event) {
            return $event->action->getAction() == FcmAction::RECOMMEND_SERVICE;
        });

        \Event::assertDispatched(function (FcmPush $event) use ($service, $car) {
            return $event->action->getBody() == __('notification.firebase.action_recommend_service.body', [
                    'service' => $service->current->name,
                    'number' => $car->number->getValue(),
                    'car' => $car->car_name
                ]);
        });
    }

    /** @test */
    public function success_only_required_field()
    {
        \Event::fake([
            FcmPush::class
        ]);
        $data = $this->data();
        $car = $this->carBuilder()->setUuid($data["auto"])->create();

        $model = Recommendation::query()->where('uuid', $data["uuid"])->first();
        $this->assertNull($model);

        unset(
            $data["comment"],
            $data["quantity"],
            $data["request"],
            $data["rejectionReason"],
            $data["dateCompletion"],
            $data["author"],
            $data["executor"],
            $data["dateRelevance"],
        );

        $res = $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($res->json('data'));
        $this->assertTrue($res->json('success'));

        $model = Recommendation::query()->where('uuid', $data["uuid"])->first();
        $this->assertNotNull($model);
        $this->assertEquals($model->car_uuid, $data["auto"]);
        $this->assertEquals($model->text, $data["recommendation"]);
        $this->assertEquals($model->completed, $data["completed"]);

        $this->assertNull($model->order_uuid);
        $this->assertNull($model->comment);
        $this->assertNull($model->qty);
        $this->assertNull($model->rejection_reason);
        $this->assertNull($model->author);
        $this->assertNull($model->executor);
        $this->assertNull($model->completion_at);
        $this->assertNull($model->relevance_at);
        $this->assertEquals($model->status, Recommendation::STATUS_NEW);
        $this->assertNotNull($model->data);

        \Event::assertDispatched(FcmPush::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);

        \Event::assertDispatched(function (FcmPush $event) {
            return $event->action->getAction() == FcmAction::RECOMMEND_SERVICE;
        });

        \Event::assertDispatched(function (FcmPush $event) use ($car) {
            return $event->action->getBody() == __('notification.firebase.action_recommend_service.body', [
                    'service' => null,
                    'number' => $car->number->getValue(),
                    'car' => $car->car_name
                ]);
        });
    }

    /** @test */
    public function success_edit()
    {
        $data = $this->data();
        $data["completed"] = true;
        $this->carBuilder()->setUuid($data["auto"])->create();
        $this->orderBuilder()->setUuid($data["request"])->create();

        $model = $this->recommendationBuilder()->create();

        $this->assertNotEquals($model->car_uuid, $data["auto"]);
        $this->assertNotEquals($model->order_uuid, $data["request"]);
        $this->assertNotEquals($model->text, $data["recommendation"]);
        $this->assertNotEquals($model->comment, $data["comment"]);
        $this->assertNotEquals($model->qty, $data["quantity"]);
        $this->assertNotEquals($model->rejection_reason, $data["rejectionReason"]);
        $this->assertNotEquals($model->author, $data["author"]);
        $this->assertNotEquals($model->executor, $data["executor"]);
        $this->assertNotEquals($model->completed, $data["completed"]);
        $this->assertNotEquals($model->completion_at, $data["dateCompletion"]);
        $this->assertNotEquals($model->relevance_at, $data["dateRelevance"]);

        $data["uuid"] = $model->uuid;

        $res = $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($res->json('data'));
        $this->assertTrue($res->json('success'));

        $model->refresh();

        $this->assertNotEquals($model->car_uuid, $data["auto"]);
        $this->assertEquals($model->order_uuid, $data["request"]);
        $this->assertEquals($model->text, $data["recommendation"]);
        $this->assertEquals($model->comment, $data["comment"]);
        $this->assertEquals($model->qty, $data["quantity"]);
        $this->assertEquals($model->rejection_reason, $data["rejectionReason"]);
        $this->assertEquals($model->author, $data["author"]);
        $this->assertEquals($model->executor, $data["executor"]);
        $this->assertEquals($model->completed, $data["completed"]);
        $this->assertEquals($model->completion_at->timestamp, $data["dateCompletion"]);
        $this->assertEquals($model->relevance_at->timestamp, $data["dateRelevance"]);
    }

    /** @test */
    public function success_edit_old_status_from_complited()
    {
        $data = $this->data();
        $this->carBuilder()->setUuid($data["auto"])->create();
        $this->orderBuilder()->setUuid($data["request"])->create();

        $model = $this->recommendationBuilder()->create();

        $this->assertTrue($model->isNew());

        $data["uuid"] = $model->uuid;
        $data["completed"] = true;

        $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $this->headers()
        )
            ->assertOk();

        $model->refresh();

        $this->assertTrue($model->isOld());
    }

    /** @test */
    public function success_edit_old_status_from_date_complition()
    {
        $data = $this->data();
        $this->carBuilder()->setUuid($data["auto"])->create();
        $this->orderBuilder()->setUuid($data["request"])->create();

        $model = $this->recommendationBuilder()->create();

        $this->assertTrue($model->isNew());

        $data["uuid"] = $model->uuid;
        $data["dateCompletion"] = Carbon::now()->timestamp;

        $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $this->headers()
        )
            ->assertOk();

        $model->refresh();

        $this->assertTrue($model->isOld());
        $this->assertEquals($model->completion_at->timestamp, $data["dateCompletion"]);
    }

    /** @test */
    public function fail_edit_old_status()
    {
        $data = $this->data();
        $this->carBuilder()->setUuid($data["auto"])->create();
        $this->orderBuilder()->setUuid($data["request"])->create();

        $model = $this->recommendationBuilder()->setStatus(Recommendation::STATUS_USED)->create();
        $model->refresh();

        $this->assertTrue($model->isUsed());

        $data["uuid"] = $model->uuid;
        $data["completed"] = true;

        $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $this->headers()
        )
            ->assertOk();

        $model->refresh();

        $this->assertTrue($model->isUsed());
    }

    /** @test */
    public function fail_not_exist_auto_uuid()
    {
        $data = $this->data();
        $this->orderBuilder()->setUuid($data["request"])->create();

        $model = Recommendation::query()->where('uuid', $data["uuid"])->first();
        $this->assertNull($model);

        $res = $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $this->headers() + ['Content-Language' => 'en']
        );

        $this->assertEquals($res->json('data'),
            __('validation.in', ['attribute' => "auto"])
        );
        $this->assertFalse($res->json('success'));
    }

    /** @test */
    public function fail_not_exist_order_uuid()
    {
        $data = $this->data();
        $this->carBuilder()->setUuid($data["auto"])->create();

        $model = Recommendation::query()->where('uuid', $data["uuid"])->first();
        $this->assertNull($model);

        $res = $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $this->headers() + ['Content-Language' => 'en']
        );

        $this->assertEquals($res->json('data'),
            __('validation.in', ['attribute' => "request"])
        );
        $this->assertFalse($res->json('success'));
    }

    /** @test */
    public function wrong_auth_token()
    {
        $data = $this->data();
        $this->carBuilder()->setUuid($data["auto"])->create();
        $this->orderBuilder()->setUuid($data["request"])->create();

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $res = $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($res->json('data'), 'Bad authorization token');
        $this->assertFalse($res->json('success'));
    }

    /** @test */
    public function without_auth_token()
    {
        $data = $this->data();
        $this->carBuilder()->setUuid($data["auto"])->create();
        $this->orderBuilder()->setUuid($data["request"])->create();

        $headers = $this->headers();
        unset($headers['Authorization']);

        $res = $this->post(
            route('api.v1.recommendation.create'),
            $data,
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($res->json('data'), 'Missing authorization header');
        $this->assertFalse($res->json('success'));
    }

    protected function data(): array
    {
        return [
            "uuid" => "9ee4670f-0016-11ec-8274-4cd98fc26f15",
            "auto" => "8ee4670f-0016-11ec-8274-4cd98fc26f15",
            "recommendation" => "Амортизатор передній Megane III,Fluence",
            "comment" => "some comment",
            "quantity" => 33,
            "request" => "ee060bad-5446-11ec-8277-4cd98fc26f14",
            "rejectionReason" => null,
            "dateCompletion" => 1624350625,
            "author" => "Коротун Сергій Юрійович",
            "executor" => "Коротун Сергій Юрійович",
            "completed" => false,
            "dateRelevance" => 1624350625
        ];
    }
}
