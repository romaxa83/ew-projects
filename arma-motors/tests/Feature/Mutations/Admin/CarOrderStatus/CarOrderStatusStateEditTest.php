<?php

namespace Tests\Feature\Mutations\Admin\CarOrderStatus;

use App\Events\Firebase\FcmPush;
use App\Exceptions\ErrorsCode;
use App\Listeners\Firebase\FcmPushListeners;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\User\Car;
use App\Services\Firebase\FcmAction;
use App\Services\User\CarService;
use App\Types\Permissions;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\UserBuilder;
use Tests\Unit\Services\User\CarServiceTest;

class CarOrderStatusStateEditTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        \Event::fake([FcmPush::class]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CAR_ORDER_STATUS_STATE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = $this->getModel()->statuses[0];

        $date = Carbon::now();

        $data['id'] = $model->id;
        $data['state'] = 'done';
        $data['dateAt'] = (string)$date->timestamp;

        $this->assertNotEquals($model->state, $data['state']);
        $this->assertNotEquals($model->date_at, $data['dateAt']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $responseData = $response->json('data.carOrderStatusStateEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('state', $responseData);
        $this->assertArrayHasKey('dateAt', $responseData);
        $this->assertEquals($responseData['state'], $data['state']);
        $this->assertEquals($responseData['dateAt'], $date);

        $model->refresh();

        $this->assertEquals($model->status, $data['state']);
        $this->assertEquals($model->date_at, $responseData['dateAt']);

        \Event::assertNotDispatched(FcmPush::class);
    }

    /** @test */
    public function success_exit_from_order_car()
    {
        \Event::fake([FcmPush::class]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CAR_ORDER_STATUS_STATE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = $this->getModel();
        $status = $model->statuses[8];

        $date = Carbon::now();

        $data['id'] = $status->id;
        $data['state'] = 'done';
        $data['dateAt'] = (string)$date->timestamp;

        $this->assertTrue($model->car->is_order);

        $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $model->refresh();

        $this->assertFalse($model->car->is_order);
        $this->assertTrue($model->car->is_verify);
        $this->assertEquals($model->car->inner_status, Car::VERIFY);

        \Event::assertDispatched(FcmPush::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);

        \Event::assertDispatched(function (FcmPush $event) {
            return $event->action->getAction() == FcmAction::CAN_ADD_CAR_TO_GARAGE;
        });
    }

    /** @test */
    public function fail_exit_from_order_car()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CAR_ORDER_STATUS_STATE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = $this->getModel();
        $status = $model->statuses[8];

        $date = Carbon::now();

        $data['id'] = $status->id;
        $data['state'] = 'skip';
        $data['dateAt'] = (string)$date->timestamp;

        $this->assertTrue($model->car->is_order);

        $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $model->refresh();

        $this->assertTrue($model->car->is_order);
    }

    /** @test */
    public function success_only_state()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CAR_ORDER_STATUS_STATE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = $this->getModel()->statuses[0];

        $data['id'] = $model->id;
        $data['state'] = 'done';

        $this->assertNotEquals($model->state, $data['state']);
        $this->assertNull($model->date_at);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyState($data)]);

        $responseData = $response->json('data.carOrderStatusStateEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('state', $responseData);
        $this->assertArrayHasKey('dateAt', $responseData);
        $this->assertEquals($responseData['state'], $data['state']);
        $this->assertNull($responseData['dateAt']);

        $model->refresh();

        $this->assertEquals($model->status, $data['state']);
        $this->assertNull($model->date_at);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CAR_ORDER_STATUS_STATE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data['id'] = 999;
        $data['state'] = 'done';

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyState($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CAR_ORDER_STATUS_STATE_EDIT)
            ->create();

        $model = $this->getModel();

        $data['id'] = $model->id;
        $data['state'] = 'done';

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyState($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = $this->getModel();

        $data['id'] = $model->id;
        $data['state'] = 'done';

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyState($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getModel()
    {
        $service = app(CarService::class);
        $user = $this->userBuilder()->create();
        $user->refresh();

        $this->assertEmpty($user->cars);

        $brand = Brand::find(1);
        $model = Model::find(10);

        $data = CarServiceTest::successDataFromAA();
        $data['vechilces'][0]['brandId'] = $brand->uuid;
        $data['vechilces'][0]['modelId'] = $model->uuid;

        $service->createFromAA($user, $data['vechilces']);
        $user->refresh();

        return $user->cars[0]->carOrder;
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                carOrderStatusStateEdit(input:{
                    id: "%s",
                    state: %s,
                    dateAt: "%s"
                }) {
                    id
                    state
                    dateAt
                }
            }',
            $data['id'],
            $data['state'],
            $data['dateAt']
        );
    }

    private function getQueryStrOnlyState(array $data): string
    {
        return sprintf('
            mutation {
                carOrderStatusStateEdit(input:{
                    id: "%s",
                    state: %s,
                }) {
                    id
                    state
                    dateAt
                }
            }',
            $data['id'],
            $data['state']
        );
    }

}




