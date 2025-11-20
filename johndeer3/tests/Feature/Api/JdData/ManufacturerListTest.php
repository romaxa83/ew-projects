<?php

namespace Tests\Feature\Api\JdData;

use App\Models\JD\Manufacturer;
use App\Models\User\User;
use App\Repositories\JD\ManufacturerRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ManufacturerListTest extends TestCase
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
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = Manufacturer::query()->where('status', true)->count();

        $this->getJson(route('api.manufacturers.list'))
            ->assertJsonStructure($this->structureResource([
                [
                    "id",
                    "jd_id",
                    "name",
                    "is_parent",
                    "created",
                    "updated",
                ]
            ]))
            ->assertJsonCount($count, 'data')
        ;
    }

    /** @test */
    public function success_as_paginator()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.manufacturers.list', ['paginator' => true]))
            ->assertJsonCount(Manufacturer::DEFAULT_PER_PAGE, 'data')
        ;
    }

    /** @test */
    public function success_only_active()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = Manufacturer::query()->where('status', true)->count();
        Manufacturer::query()->first()->update(['status' => false]);
        $countNew = Manufacturer::query()->where('status', true)->count();

        $this->assertNotEquals($count, $countNew);
        $this->assertTrue($count > $countNew);

        $this->getJson(route('api.manufacturers.list'))
            ->assertJsonCount($countNew, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(ManufacturerRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllWrap")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.manufacturers.list'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.manufacturers.list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


