<?php

namespace Tests\Feature\Api\Report\Update\Ps;

use App\Helpers\DateFormat;
use App\Helpers\ReportHelper;
use App\Models\JD\Manufacturer;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdateMachineTest extends TestCase
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
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', '!=', $md_1->eg_jd_id]
        ])->first();

        $man_1 = Manufacturer::query()->first();
        $man_2 = ModelDescription::query()->where([
            ['id', '!=', $man_1->id]
        ])->first();

        $data['machines'][] = [
            'manufacturer_id' => $man_2->id,
            'model_description_id' => $md_2->id,
            'equipment_group_id' => $md_2->equipmentGroup->id,
            'header_model_id' => $md_2->id,
            'header_brand_id' => $man_2->id,
            'serial_number_header' => '7879678KJHG',
            'machine_serial_number' => '007879678KJHG',
            'trailed_equipment_type' => '1007879678KJHG',
            'trailer_model' => '2007879678KJHG',
            'sub_manufacturer_id' => $man_2->id,
            'sub_model_description_id' => $md_2->id,
            'sub_equipment_group_id' => $md_2->equipmentGroup->id,
            'sub_machine_serial_number' => '0001',
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setMachineData([
                'manufacturer_id' => $man_1->id,
                'model_description_id' => $md_1->id,
                'equipment_group_id' => $md_1->equipmentGroup->id,
                'header_model_id' => $md_1->id,
                'header_brand_id' => $man_1->id,
                'sub_manufacturer_id' => $man_1->id,
                'sub_model_description_id' => $md_1->id,
                'sub_equipment_group_id' => $md_1->equipmentGroup->id,
            ])
            ->setUser($user)
            ->create();

        $reportMachineId = $rep->reportMachines->first()->id;
        $reportTitle = $rep->title;
        $this->assertCount(1, $rep->reportMachines);

        $this->assertNotEquals($rep->reportMachines->first()->manufacturer_id, data_get($data, 'machines.0.manufacturer_id'));
        $this->assertNotEquals($rep->reportMachines->first()->model_description_id, data_get($data, 'machines.0.model_description_id'));
        $this->assertNotEquals($rep->reportMachines->first()->equipment_group_id, data_get($data, 'machines.0.equipment_group_id'));
        $this->assertNotEquals($rep->reportMachines->first()->header_model_id, data_get($data, 'machines.0.header_model_id'));
        $this->assertNotEquals($rep->reportMachines->first()->header_brand_id, data_get($data, 'machines.0.header_brand_id'));
        $this->assertNotEquals($rep->reportMachines->first()->serial_number_header, data_get($data, 'machines.0.serial_number_header'));
        $this->assertNotEquals($rep->reportMachines->first()->machine_serial_number, data_get($data, 'machines.0.machine_serial_number'));
        $this->assertNotEquals($rep->reportMachines->first()->trailed_equipment_type, data_get($data, 'machines.0.trailed_equipment_type'));
        $this->assertNotEquals($rep->reportMachines->first()->trailer_model, data_get($data, 'machines.0.trailer_model'));
        $this->assertNotEquals($rep->reportMachines->first()->sub_manufacturer_id, data_get($data, 'machines.0.sub_manufacturer_id'));
        $this->assertNotEquals($rep->reportMachines->first()->sub_model_description_id, data_get($data, 'machines.0.sub_model_description_id'));
        $this->assertNotEquals($rep->reportMachines->first()->sub_equipment_group_id, data_get($data, 'machines.0.sub_equipment_group_id'));
        $this->assertNotEquals($rep->reportMachines->first()->sub_machine_serial_number, data_get($data, 'machines.0.sub_machine_serial_number'));

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "machine" => [
                        [
                            'manufacturer' => [
                                "id" => data_get($data, 'machines.0.manufacturer_id')
                            ],
                            'equipment_group' => [
                                "id" => data_get($data, 'machines.0.equipment_group_id')
                            ],
                            'model_description' => [
                                "id" => data_get($data, 'machines.0.model_description_id')
                            ],
                            "trailed_equipment_type" => data_get($data, 'machines.0.trailed_equipment_type'),
                            "trailer_model" => data_get($data, 'machines.0.trailer_model'),
                            "serial_number_header" => data_get($data, 'machines.0.serial_number_header'),
                            "machine_serial_number" => data_get($data, 'machines.0.machine_serial_number'),
                            "header_brand" => [
                                "id" => data_get($data, 'machines.0.header_brand_id'),
                            ],
                            "header_model" => [
                                "id" => data_get($data, 'machines.0.header_model_id'),
                            ],
                            "sub" => [
                                "machine_serial_number" => data_get($data, 'machines.0.sub_machine_serial_number'),
                                'manufacturer' => [
                                    "id" => data_get($data, 'machines.0.sub_manufacturer_id')
                                ],
                                'equipment_group' => [
                                    "id" => data_get($data, 'machines.0.sub_equipment_group_id')
                                ],
                                'model_description' => [
                                    "id" => data_get($data, 'machines.0.sub_model_description_id')
                                ],
                            ]
                        ]
                    ],
                ]
            ])
        ;

        $rep->refresh();

        $title = ReportHelper::prettyTitle($user->dealer->name .'__'.$md_2->name.'_'.DateFormat::forTitle($rep->created_at));

        $this->assertCount(1, $rep->reportMachines);
        $this->assertNotEquals($rep->reportMachines->first()->id, $reportMachineId);

        $this->assertNotEquals($reportTitle, $title);
        $this->assertEquals($rep->title, $title);
    }

    /** @test */
    public function success_change_one_field()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();

        $man_1 = Manufacturer::query()->first();
        $man_2 = ModelDescription::query()->where([
            ['id', '!=', $man_1->id]
        ])->first();

        $data['machines'][] = [
            'manufacturer_id' => $man_2->id,
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setMachineData([
                'manufacturer_id' => $man_1->id,
                'model_description_id' => $md_1->id,
                'equipment_group_id' => $md_1->equipmentGroup->id,
                'header_model_id' => $md_1->id,
                'header_brand_id' => $man_1->id,
                'sub_manufacturer_id' => $man_1->id,
                'sub_model_description_id' => $md_1->id,
                'sub_equipment_group_id' => $md_1->equipmentGroup->id,
            ])
            ->setUser($user)
            ->create();

        $reportTitle = $rep->title;

        $this->assertNotNull($rep->reportMachines->first()->manufacturer_id);
        $this->assertNotNull($rep->reportMachines->first()->model_description_id);
        $this->assertNotNull($rep->reportMachines->first()->equipment_group_id);
        $this->assertNotNull($rep->reportMachines->first()->header_model_id);
        $this->assertNotNull($rep->reportMachines->first()->header_brand_id);
        $this->assertNotNull($rep->reportMachines->first()->serial_number_header);
        $this->assertNotNull($rep->reportMachines->first()->machine_serial_number);
        $this->assertNotNull($rep->reportMachines->first()->trailed_equipment_type);
        $this->assertNotNull($rep->reportMachines->first()->trailer_model);
        $this->assertNotNull($rep->reportMachines->first()->sub_manufacturer_id);
        $this->assertNotNull($rep->reportMachines->first()->sub_model_description_id);
        $this->assertNotNull($rep->reportMachines->first()->sub_equipment_group_id);
        $this->assertNotNull($rep->reportMachines->first()->sub_machine_serial_number);

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "machine" => [
                        [
                            'manufacturer' => [
                                "id" => data_get($data, 'machines.0.manufacturer_id')
                            ],
                            'equipment_group' => null,
                            'model_description' => null,
                            "trailed_equipment_type" => null,
                            "trailer_model" => null,
                            "serial_number_header" => null,
                            "machine_serial_number" => null,
                            "header_brand" => null,
                            "header_model" => null,
                            "sub" => [
                                "machine_serial_number" => null,
                                'manufacturer' => null,
                                'equipment_group' => null,
                                'model_description' => null,
                            ]
                        ]
                    ],
                ]
            ])
        ;

        $rep->refresh();

        $title = ReportHelper::prettyTitle($user->dealer->name .'___'.DateFormat::forTitle($rep->created_at));

        $this->assertNotEquals($reportTitle, $title);
        $this->assertEquals($rep->title, $title);
    }

    /** @test */
    public function fail_not_equal_eg_and_model()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', '!=', $md_1->eg_jd_id]
        ])->first();

        $man_1 = Manufacturer::query()->first();
        $man_2 = ModelDescription::query()->where([
            ['id', '!=', $man_1->id]
        ])->first();

        $data['machines'][] = [
            'manufacturer_id' => $man_2->id,
            'model_description_id' => $md_2->id,
            'equipment_group_id' => $md_1->equipmentGroup->id,
            'header_model_id' => $md_2->id,
            'header_brand_id' => $man_2->id,
            'serial_number_header' => '7879678KJHG',
            'machine_serial_number' => '007879678KJHG',
            'trailed_equipment_type' => '1007879678KJHG',
            'trailer_model' => '2007879678KJHG',
            'sub_manufacturer_id' => $man_2->id,
            'sub_model_description_id' => $md_2->id,
            'sub_equipment_group_id' => $md_2->equipmentGroup->id,
            'sub_machine_serial_number' => '0001',
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setMachineData([
                'manufacturer_id' => $man_1->id,
                'model_description_id' => $md_1->id,
                'equipment_group_id' => $md_1->equipmentGroup->id,
                'header_model_id' => $md_1->id,
                'header_brand_id' => $man_1->id,
                'sub_manufacturer_id' => $man_1->id,
                'sub_model_description_id' => $md_1->id,
                'sub_equipment_group_id' => $md_1->equipmentGroup->id,
            ])
            ->setUser($user)
            ->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson($this->structureErrorResponse([
                "This Equipment group [{$md_1->equipmentGroup->id}] does not contain this Model Description [{$md_2->id}]"
            ]))
        ;
    }

    /** @test */
    public function fail_not_exist_eg_and_exsist_model()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', '!=', $md_1->eg_jd_id]
        ])->first();

        $man_1 = Manufacturer::query()->first();
        $man_2 = ModelDescription::query()->where([
            ['id', '!=', $man_1->id]
        ])->first();

        $data['machines'][] = [
            'manufacturer_id' => $man_2->id,
            'model_description_id' => $md_2->id,
            'equipment_group_id' => null,
            'header_model_id' => $md_2->id,
            'header_brand_id' => $man_2->id,
            'serial_number_header' => '7879678KJHG',
            'machine_serial_number' => '007879678KJHG',
            'trailed_equipment_type' => '1007879678KJHG',
            'trailer_model' => '2007879678KJHG',
            'sub_manufacturer_id' => $man_2->id,
            'sub_model_description_id' => $md_2->id,
            'sub_equipment_group_id' => $md_2->equipmentGroup->id,
            'sub_machine_serial_number' => '0001',
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setMachineData([
                'manufacturer_id' => $man_1->id,
                'model_description_id' => $md_1->id,
                'equipment_group_id' => $md_1->equipmentGroup->id,
                'header_model_id' => $md_1->id,
                'header_brand_id' => $man_1->id,
                'sub_manufacturer_id' => $man_1->id,
                'sub_model_description_id' => $md_1->id,
                'sub_equipment_group_id' => $md_1->equipmentGroup->id,
            ])
            ->setUser($user)
            ->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson($this->structureErrorResponse([
                "This Equipment group [] does not contain this Model Description [{$md_2->id}]"
            ]))
        ;
    }
}


