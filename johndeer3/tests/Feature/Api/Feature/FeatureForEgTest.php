<?php

namespace Tests\Feature\Api\Feature;

use App\Models\Report\Feature\Feature;
use Tests\TestCase;
use App\Models\User\User;
use Illuminate\Http\Response;
use Tests\Builder\UserBuilder;
use App\Models\JD\EquipmentGroup;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Feature\FeatureBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FeatureForEgTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        list($val_1, $val_2) = ["val_1", "val_2"];
        $f_1 = $this->featureBuilder->withTranslation()->setPosition(1)
            ->setEgIds($eg_1->id)->setValues($val_1, $val_2)->create();
        $f_2 = $this->featureBuilder->withTranslation()->setPosition(2)
            ->setEgIds($eg_1->id, $eg_2->id)->create();
        $f_3 = $this->featureBuilder->withTranslation()->setPosition(3)
            ->setEgIds($eg_2->id)->create();
        $f_4 = $this->featureBuilder->withTranslation()->setPosition(4)
            ->setEgIds($eg_2->id)->create();

        $this->getJson(route('api.report.features-field', ['equipmentGroup' => $eg_1->id]), [
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson(["data" => [
                [
                    "id" => $f_1->id,
                    "name" => $f_1->current->name,
                    "unit" => $f_1->current->unit,
                    "position" => $f_1->position,
                    "type" => $f_1->type,
                    "type_field" => $f_1->type_field_for_front,
                    "values" => [
                        ["id" => $f_1->values[0]->id, "name" => $val_1],
                        ["id" => $f_1->values[1]->id, "name" => $val_2],
                    ]
                ],
                [
                    "id" => $f_2->id,
                    "name" => $f_2->current->name,
                    "unit" => $f_2->current->unit
                ]
            ]])
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(2, 'data.0.values')
            ->assertJsonCount(0, 'data.1.values')
        ;

        $this->getJson(route('api.report.features-field', ['equipmentGroup' => $eg_2->id]), [
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson(["data" => [
                ["id" => $f_2->id],
                ["id" => $f_3->id],
                ["id" => $f_4->id]
            ]])
            ->assertJsonCount(3, 'data')
            ->assertJsonCount(0, 'data.0.values')
            ->assertJsonCount(0, 'data.1.values')
        ;
    }

    /** @test */
    public function success_by_type()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $f_1 = $this->featureBuilder->withTranslation()->setPosition(1)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_GROUND)->create();
        $f_2 = $this->featureBuilder->withTranslation()->setPosition(2)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_MACHINE)->create();
        $f_3 = $this->featureBuilder->withTranslation()->setPosition(3)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_GROUND)->create();
        $f_4 = $this->featureBuilder->withTranslation()->setPosition(4)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_MACHINE)->create();

        $this->getJson(route('api.report.features-field', [
            'equipmentGroup' => $eg_1->id,
            'type' => Feature::TYPE_GROUND
        ]))
            ->assertJson(["data" => [
                ["id" => $f_1->id],
                ["id" => $f_3->id],
            ]])
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('api.report.features-field', [
            'equipmentGroup' => $eg_1->id,
            'type' => Feature::TYPE_MACHINE
        ]))
            ->assertJson(["data" => [
                ["id" => $f_2->id],
                ["id" => $f_4->id],
            ], "success" => true])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_by_type_as_null()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $f_1 = $this->featureBuilder->withTranslation()->setPosition(1)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_GROUND)->create();
        $f_2 = $this->featureBuilder->withTranslation()->setPosition(2)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_MACHINE)->create();
        $f_3 = $this->featureBuilder->withTranslation()->setPosition(3)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_GROUND)->create();
        $f_4 = $this->featureBuilder->withTranslation()->setPosition(4)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_MACHINE)->create();

        $this->getJson(route('api.report.features-field', [
            'equipmentGroup' => $eg_1->id,
            'type' => null
        ]))
            ->assertJson(["data" => [
                ["id" => $f_1->id],
                ["id" => $f_2->id],
                ["id" => $f_3->id],
                ["id" => $f_4->id],
            ]])
            ->assertJsonCount(4, 'data')
        ;
    }

    /** @test */
    public function success_wrong_type()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $f_1 = $this->featureBuilder->withTranslation()->setPosition(1)
            ->setEgIds($eg_1->id)->setType(Feature::TYPE_GROUND)->create();

        $this->getJson(route('api.report.features-field', [
            'equipmentGroup' => $eg_1->id,
            'type' => 'wrong'
        ]))
            ->assertJson([])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_only_active()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $f_1 = $this->featureBuilder->withTranslation()
            ->setEgIds($eg_1->id)->create();
        $f_2 = $this->featureBuilder->withTranslation()
            ->setActive(false)->setEgIds($eg_1->id)->create();

        $this->getJson(route('api.report.features-field', ['equipmentGroup' => $eg_1->id]), [
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson(["data" => [
                ["id" => $f_1->id]
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_if_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $this->getJson(route('api.report.features-field', ['equipmentGroup' => $eg_1->id]), [
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson(["data" => []])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_nopt_found_eg()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.report.features-field', ['equipmentGroup' => 9999]))
            ->assertJson($this->structureErrorResponse(__("message.exceptions.not found", [
                'field' => 'id',
                'value' => 9999,
            ])))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $eg_1 = EquipmentGroup::query()->first();

        $this->getJson(route('api.report.features-field', ['equipmentGroup' => $eg_1->id]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
