<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\JD\ModelDescription;
use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class ModelDescriptionTest extends TestCase
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
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where('id', '!=', $md_1->id)->first();

        $date = Carbon::now();

        $rep_1 = $this->reportBuilder->setModelDescription($md_1)
            ->setCreatedAt($date->subMinutes(2))->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setModelDescription($md_1)
            ->setCreatedAt($date->subMinutes(3))->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setModelDescription($md_2)
            ->setCreatedAt($date->subMinutes(4))->setUser($user)->create();

        $this->getJson(route('api.v2.reports', ['model_description_id' => $md_1->id]))
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

        $this->getJson(route('api.v2.reports', ['model_description_id' => $md_2->id]))
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
    }

    /** @test */
    public function success_some_value()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where('id', '!=', $md_1->id)->first();

        $date = Carbon::now();

        $rep_1 = $this->reportBuilder->setCreatedAt($date->subMinutes(1))
            ->setModelDescription($md_1)->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt($date->subMinutes(2))
            ->setModelDescription($md_1)->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setCreatedAt($date->subMinutes(3))
            ->setModelDescription($md_2)->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'model_description_id' => [
                $md_1->id,
                $md_2->id,
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

        $md = ModelDescription::query()->first();

        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setModelDescription($md)->setUser($user)->create();

        /** @var $res TestResponse */
        $res = $sendRequest->call($this);
        $res
            ->assertJson([
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
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['model_description_id' => 'null']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['model_description_id' => null]));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['model_description_id' => '']));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['model_description_id' => 9999]));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['model_description_id' => []]));
                },
                2
            ]
        ];
    }
}

