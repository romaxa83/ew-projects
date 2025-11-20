<?php

namespace Tests\Feature\Api\Report\Admin\Update;

use App\Models\JD\Manufacturer;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Type\ReportStatus;
use Tests\Feature\Api\Report\Create\CreateTest;
use Tests\TestCase;
use Tests\Builder\UserBuilder;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Report\ReportBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateMachineDataTest extends TestCase
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
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = CreateTest::fullData();
        $data['title'] = 'update_title';
        unset($data['location']);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', '!=', $md_1->eg_jd_id]
        ])->first();

        $man_1 = Manufacturer::query()->first();
        $man_2 = ModelDescription::query()->where([
            ['id', '!=', $man_1->id]
        ])->first();

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

        $data['machines'][] = [
            'id' => $rep->reportMachines->first()->id,
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

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep->id,
                'machine' => [
                    [
                        "id" => data_get($data, "machines.0.id"),
                        "manufacturer" => [
                            "id" => $rep->reportMachines->first()->manufacturer_id
                        ],
                        "equipment_group" => [
                            "id" => data_get($data, "machines.0.equipment_group_id")
                        ],
                        "model_description" => [
                            "id" => data_get($data, "machines.0.model_description_id")
                        ],
                        "trailed_equipment_type" => data_get($data, "machines.0.trailed_equipment_type"),
                        "trailer_model" => data_get($data, "machines.0.trailer_model"),
                        "serial_number_header" => data_get($data, "machines.0.serial_number_header"),
                        "machine_serial_number" => data_get($data, "machines.0.machine_serial_number"),
                        "header_brand" => [
                            "id" => data_get($data, "machines.0.header_brand_id")
                        ],
                        "header_model" => [
                            "id" => data_get($data, "machines.0.header_model_id")
                        ],
                        "sub" => [
                            "machine_serial_number" => $rep->reportMachines->first()->sub_machine_serial_number,
                            "manufacturer" => [
                                "id" => $rep->reportMachines->first()->sub_manufacturer_id
                            ],
                            "equipment_group" => [
                                "id" => $rep->reportMachines->first()->sub_equipment_group_id
                            ],
                            "model_description" => [
                                "id" => $rep->reportMachines->first()->sub_model_description_id
                            ],
                        ]
                    ]
                ],
            ]))
        ;
    }

    /** @test */
    public function success_empty_data()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = CreateTest::fullData();
        $data['title'] = 'update_title';
        unset($data['location']);

        $md_1 = ModelDescription::query()->first();

        $man_1 = Manufacturer::query()->first();

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

        $data['machines'][] = [
            'id' => $rep->reportMachines->first()->id
        ];

        $this->assertCount(1, $rep->reportMachines);


        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep->id,
                'machine' => [
                    [
                        "id" => data_get($data, "machines.0.id"),
                        "manufacturer" => [
                            "id" => $rep->reportMachines->first()->manufacturer_id
                        ],
                        "equipment_group" => [
                            "id" =>  $rep->reportMachines->first()->equipment_group_id
                        ],
                        "model_description" => [
                            "id" => $rep->reportMachines->first()->model_description_id
                        ],
                        "trailed_equipment_type" => $rep->reportMachines->first()->trailed_equipment_type,
                        "trailer_model" => $rep->reportMachines->first()->trailer_model,
                        "serial_number_header" => $rep->reportMachines->first()->serial_number_header,
                        "machine_serial_number" => $rep->reportMachines->first()->machine_serial_number,
                        "header_brand" => [
                            "id" => $rep->reportMachines->first()->header_brand_id
                        ],
                        "header_model" => [
                            "id" => $rep->reportMachines->first()->header_model_id
                        ],
                        "sub" => [
                            "machine_serial_number" => $rep->reportMachines->first()->sub_machine_serial_number,
                            "manufacturer" => [
                                "id" => $rep->reportMachines->first()->sub_manufacturer_id
                            ],
                            "equipment_group" => [
                                "id" => $rep->reportMachines->first()->sub_equipment_group_id
                            ],
                            "model_description" => [
                                "id" => $rep->reportMachines->first()->sub_model_description_id
                            ],
                        ]
                    ]
                ],
            ]))
        ;
    }

    /** @test */
    public function fail_without_machine_id()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->create();

        $data['machines'][] = [];

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureErrorResponse(["The machines.0.id field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_machine_id()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->create();

        $data['machines'][] = ["id" => 9999];

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureErrorResponse(["The selected machines.0.id is invalid."]))
        ;
    }

    /**
     * @test
     * @dataProvider validate_machine
     */
    public function validate_data($field, $value, $msg)
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $man_1 = Manufacturer::query()->first();
        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setMachineData([
                'manufacturer_id' => $man_1->id,
            ])
            ->setUser($user)->create();


        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), [
            'machines' => [
                [
                    "id" => $rep->reportMachines->first()->id,
                    $field => $value
                ]
            ]
        ])
            ->assertJson($this->structureErrorResponse([$msg]))
        ;
    }

    public function validate_machine(): array
    {
        return [
            ['equipment_group_id', 'ss', 'The machines.0.equipment_group_id must be an integer.'],
            ['equipment_group_id', 9999, 'The selected machines.0.equipment_group_id is invalid.'],
            ['model_description_id', 'ss', 'The machines.0.model_description_id must be an integer.'],
            ['model_description_id', 99999, 'The selected machines.0.model_description_id is invalid.'],
            ['header_brand_id', 'ss', 'The machines.0.header_brand_id must be an integer.'],
            ['header_brand_id', 99999, 'The selected machines.0.header_brand_id is invalid.'],
            ['header_model_id', 'ss', 'The machines.0.header_model_id must be an integer.'],
            ['header_model_id', 99999, 'The selected machines.0.header_model_id is invalid.'],
            ['serial_number_header', 99999, 'The machines.0.serial_number_header must be a string.'],
            ['machine_serial_number', 99999, 'The machines.0.machine_serial_number must be a string.'],
            ['trailed_equipment_type', 99999, 'The machines.0.trailed_equipment_type must be a string.'],
            ['trailer_model', 99999, 'The machines.0.trailer_model must be a string.'],
        ];
    }
}

