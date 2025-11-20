<?php

namespace Tests\Feature\Api\Country;

use App\Models\Country;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\Catalog\CountryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $model Country */
        $model = Country::query()->first();

        $this->assertTrue($model->isActive());

        $this->postJson(route('admin.country.toggle-active', [
            'country' => $model
        ]))
            ->assertJson($this->structureSuccessResponse([
                "id" => $model->id,
                "active" => false
            ]))
        ;

        $this->postJson(route('admin.country.toggle-active', [
            'country' => $model
        ]))
            ->assertJson($this->structureSuccessResponse([
                "id" => $model->id,
                "active" => true
            ]))
        ;
    }

    /** @test */
    public function fail_toggle_active_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(CountryService::class, function(MockInterface $mock){
            $mock->shouldReceive("toggleActive")
                ->andThrows(\Exception::class, "some exception message");
        });

        /** @var $model Country */
        $model = Country::query()->first();

        $this->postJson(route('admin.country.toggle-active', [
            'country' => $model
        ]))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $model Country */
        $model = Country::query()->first();

        $this->postJson(route('admin.country.toggle-active', [
            'country' => $model
        ]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Country */
        $model = Country::query()->first();

        $this->postJson(route('admin.country.toggle-active', [
            'country' => $model
        ]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

