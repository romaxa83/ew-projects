<?php

namespace Tests\Feature\Api\JdData;

use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\User\User;
use App\Repositories\JD\ModelDescriptionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ModelDescriptionListTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.model-descriptions'))
            ->assertJsonStructure($this->structureResource([
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
                    "type",
                ]
            ]))
        ;
    }

    /** @test */
    public function success_only_active()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = ModelDescription::query()->where('status', true)->count();
        ModelDescription::query()->first()->update(['status' => false]);
        $countNew = ModelDescription::query()->where('status', true)->count();

        $this->assertNotEquals($count, $countNew);
        $this->assertTrue($count > $countNew);

        $this->getJson(route('api.model-descriptions'))
            ->assertJsonCount($countNew, 'data')
        ;
    }

    /** @test */
    public function success_by_eg_id()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg = EquipmentGroup::query()->first();
        $count = ModelDescription::query()->where([
            ['eg_jd_id', $eg->jd_id],
            ['status', true],
        ])->count();

        $this->assertNotEquals($count, 0);

        $this->getJson(route('api.model-descriptions',["eg_id" => $eg->id]))
            ->assertJsonCount($count, 'data')
        ;
    }

    /** @test */
    public function success_only_exist_report()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where('id', '!=', $md_1->id)->first();
        $md_3 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['id', '!=', $md_2->id],
        ])->first();

        $this->reportBuilder->setMachineData([
            'model_description_id' => $md_1->id,
        ])->create();
        $this->reportBuilder->setMachineData([
            'model_description_id' => $md_2->id,
        ])->create();
        $this->reportBuilder->setMachineData([
            'model_description_id' => $md_3->id,
        ])->create();

        $this->getJson(route('api.model-descriptions',["only_exist_report" => true]))
            ->assertJson($this->structureSuccessResponse([
                ['id' => $md_1->id],
                ['id' => $md_2->id],
                ['id' => $md_3->id],
            ]))
            ->assertJsonCount(3, 'data')
        ;

        $count = ModelDescription::query()->where('status', true)->count();

        $this->getJson(route('api.model-descriptions',["only_exist_report" => false]))
            ->assertJsonCount($count, 'data')
        ;
    }

//    /** @test */
//    public function success_by_eg_id_for_statistics()
//    {
//        /** @var $user User */
//        $user = $this->userBuilder->create();
//        $this->loginAsUser($user);
//
//        $egId_1 = 1;
//        $egId_2 = 2;
//
//        $md_1 = ModelDescription::query()->where([
//            ['eg_jd_id', $egId_1],
//            ['status', true],
//        ])->first();
//        $md_2 = ModelDescription::query()->where([
//            ['id', '!=', $md_1->id],
//            ['eg_jd_id', $egId_1],
//            ['status', true],
//        ])->first();
//
//        $md_3 = ModelDescription::query()->where([
//            ['eg_jd_id', $egId_2],
//            ['status', true],
//        ])->first();
//
//        $this->reportBuilder->setMachineData([
//                'model_description_id' => $md_1->id,
//                'equipment_group_id' => $egId_1,
//            ])
//            ->setUser($user)
//            ->create();
//        $this->reportBuilder->setMachineData([
//            'model_description_id' => $md_2->id,
//            'equipment_group_id' => $egId_1,
//        ])
//            ->setUser($user)
//            ->create();
//        $this->reportBuilder->setMachineData([
//            'model_description_id' => $md_3->id,
//            'equipment_group_id' => $egId_2,
//        ])
//            ->setUser($user)
//            ->create();
//
//        $this->getJson(route('api.model-descriptions',[
//            "eg_id" => $egId_1,
//            "forStatistic" => false,
//        ]))
//            ->assertJson($this->structureResource([
//                $md_1->id => $md_1->name,
//                $md_2->id => $md_2->name,
//            ]))
//            ->assertJsonCount(2, 'data')
//        ;
//
//        $this->getJson(route('api.model-descriptions',[
//            "eg_id" => $egId_2,
//            "forStatistic" => "true",
//        ]))
//            ->assertJson($this->structureResource([
//                $md_3->id => $md_3->name
//            ]))
//            ->assertJsonCount(1, 'data')
//        ;
//    }

    /** @test */
    public function fail_wrong_eg_id()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $egId = 9999;

        $count = ModelDescription::query()->where('status', true)->count();

        $this->getJson(route('api.model-descriptions',["eg_id" => $egId]))
            ->assertJson($this->structureErrorResponse(["The selected eg id is invalid."]))
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(ModelDescriptionRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAll")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.model-descriptions'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.model-descriptions'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

