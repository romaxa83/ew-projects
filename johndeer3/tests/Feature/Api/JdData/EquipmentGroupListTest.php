<?php

namespace Tests\Feature\Api\JdData;

use App\Models\JD\EquipmentGroup;
use App\Models\User\User;
use App\Repositories\JD\EquipmentGroupRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class EquipmentGroupListTest extends TestCase
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

        $count = EquipmentGroup::query()->where('status', true)->count();

        $this->getJson(route('api.equipment-groups.list'))
            ->assertJsonStructure($this->structureResource([
                [
                    "id",
                    "jd_id",
                    "name",
                    "created",
                    "updated",
                    "egs",
                    "model_descriptions" => [
                        [
                            "id",
                            "jd_id",
                            "eg_jd_id",
                            "name",
                            "status",
                            "created",
                            "updated",
                            "size",
                            "size_parameter",
                            "size_parameter",
                            "type",
                        ]
                    ]
                ]
            ]))
            ->assertJsonCount($count, 'data')
        ;
    }

    /** @test */
    public function success_only_active()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = EquipmentGroup::query()->where('status', true)->count();
        EquipmentGroup::query()->first()->update(['status' => false]);
        $countNew = EquipmentGroup::query()->where('status', true)->count();

        $this->assertNotEquals($count, $countNew);
        $this->assertTrue($count > $countNew);

        $this->getJson(route('api.equipment-groups.list', ["withoutMD" => true]))
            ->assertJsonCount($countNew, 'data')
        ;
    }

    /** @test */
    public function success_without_model_description()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $res = $this->getJson(route('api.equipment-groups.list', ["withoutMD" => true]))
            ->assertJsonStructure($this->structureResource([
                "*" => [
                    "id",
                    "jd_id",
                    "name",
                    "created",
                    "updated",
                    "egs",
                ]
            ]))
        ;

        $this->assertNull($res->json('data.0.model_descriptions'));
    }

    /** @test */
    public function success_for_statistic()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = EquipmentGroup::query()->where('for_statistic', true)->count();

        $this->assertNotEquals($count, 0);
        $this->assertCount($count, EquipmentGroup::forStatistics());

        $res = $this->getJson(route('api.equipment-groups.list', ['forStatistic' => true]))
            ->assertJsonCount($count, 'data')
        ;

        foreach ($res->json('data') as $item){
            $this->assertTrue(in_array($item , EquipmentGroup::forStatistics()));
        }
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(EquipmentGroupRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAll")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.equipment-groups.list'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.equipment-groups.list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


