<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\User\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class MachineSerialNumberTest extends TestCase
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

        $rep_1 = $this->reportBuilder->setMachineData([
            'machine_serial_number' => 'MxRT22'
        ])->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setMachineData([
            'machine_serial_number' => 'MxRT23452'
        ])->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setMachineData([
            'machine_serial_number' => 'ARTdd8'
        ])->setUser($user)->create();

        $this->getJson(route('api.v2.reports', ['machine_serial_number' => 'MxR']))
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

        $this->getJson(route('api.v2.reports', ['machine_serial_number' => 'MxRT22']))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                ],
                "meta" => [
                    "total" => 1,
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.v2.reports', ['machine_serial_number' => 'ARTdd8']))
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

        $this->reportBuilder->setMachineData([
            'machine_serial_number' => 'MxRT22'
        ])->setUser($user)->create();
        $this->reportBuilder->setMachineData([
            'machine_serial_number' => 'MxRT22'
        ])->setUser($user)->create();

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
                    return $this->getJson(route('api.v2.reports', ['machine_serial_number' => 'null']));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['machine_serial_number' => null]));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['machine_serial_number' => '']));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['machine_serial_number' => 'SRTT09']));
                },
                0
            ],
        ];
    }
}


