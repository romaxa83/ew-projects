<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerCrud;

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        $sales_manager = $this->userBuilder->asSalesManager()->create();

        /** @var $model Customer */
        $model = $this->customerBuilder->salesManager($sales_manager)->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.customers.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $sales_manager->refresh();

        $this->assertFalse(Customer::query()->where('id', $id)->exists());
        $this->assertNotNull($sales_manager);
    }

    /** @test */
    public function success_delete_as_sales_manager()
    {
        $sales = $this->loginUserAsSalesManager();

        /** @var $model Customer */
        $model = $this->customerBuilder->salesManager($sales)->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.customers.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Customer::query()->where('id', $id)->exists());
    }


    /** @test */
    public function fail_delete_as_sales_manager_not_owner()
    {
        $this->loginUserAsSalesManager();

        $sales_manager = $this->userBuilder->asSalesManager()->create();

        /** @var $model Customer */
        $model = $this->customerBuilder->salesManager($sales_manager)->create();

        $id = $model->id;

        $res = $this->deleteJson(route('api.v1.customers.delete', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.customer.cant_delete_not_owner'), Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertTrue(Customer::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_has_trailer_and_truck()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $this->truckBuilder->customer($model)->create();
        $this->trailerBuilder->customer($model)->create();

        $id = $model->id;

        $res = $this->deleteJson(route('api.v1.customers.delete', ['id' => $model->id]));

        $truckLink = str_replace('{id}', $model->id, config('routes.front.trucks_with_customer_filter_url'));
        $trailerLink = str_replace('{id}', $model->id, config('routes.front.trailers_with_customer_filter_url'));

        self::assertErrorMsg($res, __("exceptions.customer.has_truck_and_trailer", [
            'trucks' => $truckLink,
            'trailers' => $trailerLink
        ]), Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertTrue(Customer::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_has_trailer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $this->trailerBuilder->customer($model)->create();

        $id = $model->id;

        $res = $this->deleteJson(route('api.v1.customers.delete', ['id' => $model->id]));

        $trailerLink = str_replace('{id}', $model->id, config('routes.front.trailers_with_customer_filter_url'));

        self::assertErrorMsg($res, __("exceptions.customer.has_trailer", [
            'trailers' => $trailerLink
        ]), Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertTrue(Customer::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_has_truck()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $this->truckBuilder->customer($model)->create();

        $id = $model->id;

        $res = $this->deleteJson(route('api.v1.customers.delete', ['id' => $model->id]));

        $truckLink = str_replace('{id}', $model->id, config('routes.front.trucks_with_customer_filter_url'));

        self::assertErrorMsg($res, __("exceptions.customer.has_truck", [
            'trucks' => $truckLink
        ]), Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertTrue(Customer::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.customers.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->deleteJson(route('api.v1.customers.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->deleteJson(route('api.v1.customers.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
