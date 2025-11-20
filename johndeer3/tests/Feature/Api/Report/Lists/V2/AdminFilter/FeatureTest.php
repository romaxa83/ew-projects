<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\Report\Feature\Feature;
use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class FeatureTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $val_1 = 'val_1';
        $val_2 = 'val_2';
        $val_3 = 'val_3';
        $feature = $this->featureBuilder
            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
            ->setValues($val_1, $val_2, $val_3)
            ->withTranslation()
            ->create();

        $date = Carbon::now();
        $rep_1 = $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])->setCreatedAt($date->subMinutes(2))->create();
        $rep_2 = $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])->setCreatedAt($date->subMinutes(3))->create();
        $rep_3 = $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[1]->id]
                ]]
            ])->setCreatedAt($date->subMinutes(4))->create();
        $rep_4 = $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[2]->id]
                ]]
            ])->setCreatedAt($date->subMinutes(5))->create();

        // запрос
        $this->getJson(route('api.v2.reports', [
            'feature_value_id' => $feature->values[0]->id
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                ],
                "meta" => [
                    "total" => 2,
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'feature_value_id' => $feature->values[1]->id
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_3->id],
                ],
                "meta" => [
                    "total" => 1,
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'feature_value_id' => $feature->values[2]->id
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_4->id],
                ],
                "meta" => [
                    "total" => 1,
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_some_value()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $val_1 = 'val_1';
        $val_2 = 'val_2';
        $val_3 = 'val_3';
        $feature = $this->featureBuilder
            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
            ->setValues($val_1, $val_2, $val_3)
            ->withTranslation()
            ->create();

        $date = Carbon::now();
        $rep_1 = $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])->setCreatedAt($date->subMinutes(2))->create();
        $rep_2 = $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])->setCreatedAt($date->subMinutes(3))->create();
        $rep_3 = $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[1]->id]
                ]]
            ])->setCreatedAt($date->subMinutes(4))->create();
        $rep_4 = $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[2]->id]
                ]]
            ])->setCreatedAt($date->subMinutes(5))->create();

        // запрос
        $this->getJson(route('api.v2.reports', [
            'feature_value_id' => [
                $feature->values[0]->id,
                $feature->values[1]->id,
            ]
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_3->id],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'feature_value_id' => [
                $feature->values[1]->id,
                $feature->values[2]->id
            ]
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_3->id],
                    ["id" => $rep_4->id],
                ],
                "meta" => [
                    "total" => 2,
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /**
     * @test
     * @dataProvider requests
     */
    public function ignore_query_if_wrong(\Closure $sendRequest, $count)
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $val_1 = 'val_1';
        $feature = $this->featureBuilder
            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
            ->setValues($val_1)
            ->withTranslation()
            ->create();

        $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])->create();
        $this->reportBuilder->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])->create();

        /** @var $res TestResponse */
        $res = $sendRequest->call($this);
        $res->assertJson([
            "meta" => [
                "total" => $count,
            ]
        ])
            ->assertJsonCount($count, 'data')
        ;
    }

    public function requests(): array
    {
        return [
//            [
//                function (){
//                    return $this->getJson(route('api.v2.reports', ['feature_value_id' => 'null']));
//                },
//                2
//            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['feature_value_id' => null]));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['feature_value_id' => '']));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['feature_value_id' => 9999]));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['feature_value_id' => []]));
                },
                2
            ]
        ];
    }
}


