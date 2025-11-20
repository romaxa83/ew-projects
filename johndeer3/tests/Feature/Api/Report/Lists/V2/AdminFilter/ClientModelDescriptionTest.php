<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\JD\Client;
use App\Models\JD\ModelDescription;
use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class ClientModelDescriptionTest extends TestCase
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

        $client = Client::query()->first();

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where('id', '!=', $md_1->id)->first();

        $date = Carbon::now();
        $rep_1 = $this->reportBuilder->setClientJD($client, ['model_description_id' => $md_1->id])
            ->setCreatedAt($date->subMinutes(2))->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setClientJD($client, ['model_description_id' => $md_1->id])
            ->setCreatedAt($date->subMinutes(3))->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setClientCustom(['comment' => 'some'], ['model_description_id' => $md_1->id])
            ->setCreatedAt($date->subMinutes(4))->setUser($user)->create();

        $rep_4 = $this->reportBuilder->setClientJD($client, ['model_description_id' => $md_2->id])
            ->setCreatedAt($date->subMinutes(5))->setUser($user)->create();
        $rep_5 = $this->reportBuilder->setClientJD($client, ['model_description_id' => $md_2->id])
            ->setCreatedAt($date->subMinutes(6))->setUser($user)->create();

        $this->getJson(route('api.v2.reports', ['client_model_description_id' => $md_1->id]))
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

        $this->getJson(route('api.v2.reports', ['client_model_description_id' => $md_2->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_4->id],
                    ["id" => $rep_5->id],
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

        $client = Client::query()->first();

        $md_1 = ModelDescription::query()->first();

        $this->reportBuilder->setClientJD($client, ['model_description_id' => $md_1->id])
            ->setUser($user)->create();
        $this->reportBuilder->setClientJD($client, ['model_description_id' => $md_1->id])
            ->setUser($user)->create();

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
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['client_model_description_id' => 'null']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['client_model_description_id' => null]));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['client_model_description_id' => '']));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['client_model_description_id' => 999999]));
                },
                0
            ]
        ];
    }
}
