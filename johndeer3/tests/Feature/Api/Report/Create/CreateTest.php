<?php

namespace Tests\Feature\Api\Report\Create;

use App\Helpers\DateFormat;
use App\Helpers\ReportHelper;
use App\Models\JD\Client;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\Manufacturer;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ClientType;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class CreateTest extends TestCase
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
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $user->load(['dealer.nationality']);
        $this->loginAsUser($user);
        // 130
        $modelDescriptions = ModelDescription::query()
            ->with(['product.sizeParameter',])
            ->where('id', 130)
            ->first();

        $modelDescriptions_2 = ModelDescription::query()
            ->where('id', '!=', $modelDescriptions->id)
            ->first();

        $modelDescriptions_3 = ModelDescription::query()
            ->where('id', '!=', $modelDescriptions->id)
            ->where('id', '!=', $modelDescriptions_2->id)
            ->first();

        $man = Manufacturer::query()->first();
        $man_2 = Manufacturer::query()->where('id', '!=', $man->id)->first();
        /** @var $client Client*/
        $client = Client::query()->first();

        $data = self::data() + self::fullData();

        $data['machines'][] = [
            'manufacturer_id' => $man->id,
            'model_description_id' => $modelDescriptions->id,
            'equipment_group_id' => $modelDescriptions->equipmentGroup->id,
            'header_model_id' => $modelDescriptions_2->id,
            'header_brand_id' => $man->id,
            'serial_number_header' => '7879678KJHG',
            'machine_serial_number' => '007879678KJHG',
            'trailed_equipment_type' => '1007879678KJHG',
            'trailer_model' => '2007879678KJHG',
            'sub_manufacturer_id' => $man_2->id,
            'sub_model_description_id' => $modelDescriptions_3->id,
            'sub_equipment_group_id' => $modelDescriptions_3->equipmentGroup->id,
            'sub_machine_serial_number' => '0001',
        ];
        // john_dear_client
        $data['clients'][] = [
            'client_id' => $client->id,
            'type' => ClientType::TYPE_COMPETITOR,
            'model_description_id' => $modelDescriptions_3->id,
            'quantity_machine' => 30
        ];
        // report client
        $data['clients'][] = [
            'customer_id' => 'customer_id_1',
            'customer_first_name' => 'customer_first_name_1',
            'customer_last_name' => 'customer_last_name_1',
            'company_name' => 'company_name_1',
            'customer_phone' => 'customer_phone_1',
            'comment' => 'comment_1'
        ];

        $title = $user->dealer->name.'_'.$client->company_name.'_'.$modelDescriptions->name .'_'.Carbon::now()->format('d-m-Y');

        $res = $this->postJson(route('api.report.create'), $data)
//            ->dump()
            ->assertJson([
                'data' => [
                    'title' => ReportHelper::prettyTitle($title),
                    'status' => ReportStatus::CREATED,
                    'user' => [
                        'id' => $user->id,
                        'login' => $user->login,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'status' => $user->status,
                        'created' => DateFormat::front($user->created_at),
                        'updated' => DateFormat::front($user->updated_at),
                        'role' => [
                            'alias' => $role->role
                        ],
                        'lang' => $user->lang,
                        'country' => null
                    ],
                    'dealer' => [
                        "id" => $user->dealer->id,
                        "jd_id" => $user->dealer->jd_id,
                        "jd_jd_id" => $user->dealer->jd_jd_id,
                        "name" => $user->dealer->name,
                        "status" => $user->dealer->status,
                        "country" => [
                            "id" => $user->dealer->nationality->id,
                            "name" => $user->dealer->nationality->name,
                            "alias" => $user->dealer->nationality->alias,
                        ]
                    ],
                    'machine' => [
                        [
                            'manufacturer' => [
                                'id' => $man->id,
                                'name' => $man->name
                            ],
                            'equipment_group' => [
                                'id' => $modelDescriptions->equipmentGroup->id,
                                'name' => $modelDescriptions->equipmentGroup->name
                            ],
                            'model_description' => [
                                'id' => $modelDescriptions->id,
                                'jd_id' => $modelDescriptions->jd_id,
                                'eg_jd_id' => $modelDescriptions->eg_jd_id,
                                'name' => $modelDescriptions->name,
                                'status' => $modelDescriptions->status,
                                'size' => $modelDescriptions->product->size_name,
                                'size_parameter' => $modelDescriptions->product->sizeParameter->name ?? null,
                                'type' => $modelDescriptions->product->type,
                            ],
                            'trailed_equipment_type' => data_get($data, 'machines.0.trailed_equipment_type'),
                            'trailer_model' => data_get($data, 'machines.0.trailer_model'),
                            'header_brand' => [
                                'id' => $man->id,
                                'name' => $man->name
                            ],
                            'header_model' => [
                                'id' => $modelDescriptions_2->id,
                                'name' => $modelDescriptions_2->name,
                            ],
                            'serial_number_header' => data_get($data, 'machines.0.serial_number_header'),
                            'machine_serial_number' => data_get($data, 'machines.0.machine_serial_number'),
                            'sub' => [
                                'machine_serial_number' => data_get($data, 'machines.0.sub_machine_serial_number'),
                                'manufacturer' => [
                                    'id' => $man_2->id,
                                    'name' => $man_2->name
                                ],
                                'equipment_group' => [
                                    'id' => $modelDescriptions_3->equipmentGroup->id,
                                    'name' => $modelDescriptions_3->equipmentGroup->name
                                ],
                                'model_description' => [
                                    'id' => $modelDescriptions_3->id,
                                    'name' => $modelDescriptions_3->name
                                ]
                            ]
                        ]
                    ],
                    'clients' => [
                        'john_dear_client' => [
                            [
                                'id' => $client->id,
                                'jd_id' => $client->jd_id,
                                'customer_id' => $client->customer_id,
                                'customer_first_name' => $client->customer_first_name,
                                'customer_last_name' => $client->customer_last_name,
                                'customer_second_name' => $client->customer_second_name,
                                'company_name' => $client->company_name,
                                'phone' => $client->phone,
                                'status' => $client->status,
                                'type' => data_get($data, 'clients.0.type'),
                                'quantity_machine' => data_get($data, 'clients.0.quantity_machine'),
                                'model_description' => [
                                    'id' => $modelDescriptions_3->id,
                                    'jd_id' => $modelDescriptions_3->jd_id,
                                    'eg_jd_id' => $modelDescriptions_3->eg_jd_id,
                                    'name' => $modelDescriptions_3->name,
                                ],
                                'region' => [
                                    'id' => $client->region->id,
                                    'jd_id' => $client->region->jd_id,
                                    'name' => $client->region->name,
                                    'status' => $client->region->status,
                                ]
                            ]
                        ],
                        'report_client' => [
                            [
                                "customer_id" => data_get($data, 'clients.1.customer_id'),
                                "customer_first_name" => data_get($data, 'clients.1.customer_first_name'),
                                "customer_last_name" => data_get($data, 'clients.1.customer_last_name'),
                                "company_name" => data_get($data, 'clients.1.company_name'),
                                "phone" => data_get($data, 'clients.1.customer_phone'),
                                "status" => 1,
                                "type" => null,
                                "model_description" => null,
                                "quantity_machine" => null,
                                "comment" => data_get($data, 'clients.1.comment'),
                            ]
                        ],
                    ],
                    'location' => [
                        'lat' => data_get($data, 'location.location_lat'),
                        'long' => data_get($data, 'location.location_long'),
                        'country' => data_get($data, 'location.location_country'),
                        'city' => data_get($data, 'location.location_city'),
                        'region' => data_get($data, 'location.location_region'),
                        'zipcode' => data_get($data, 'location.location_zipcode'),
                        'street' => data_get($data, 'location.location_street'),
                    ],
                    'images' => [],
                    'comment' => null,
                    'salesman_name' => data_get($data, 'salesman_name'),
                    'assignment' => data_get($data, 'assignment'),
                    'result' => data_get($data, 'result'),
                    'client_comment' => data_get($data, 'client_comment'),
                    'client_email' => data_get($data, 'client_email'),
                    'fill_table_date' => '',
                    'verify' => null,
                    'video' => null,
                    'features' => [],
                    'planned_at' => null,
                ],
                "success" => true
            ])
        ;

        $report = Report::query()->where('id', $res->json('data.id'))->first();

        $this->assertNull($report->pushData->planned_at);
        $this->assertNull($report->pushData->prev_planned_at);
        $this->assertFalse($report->pushData->is_send_start_day);
        $this->assertFalse($report->pushData->is_send_end_day);
        $this->assertFalse($report->pushData->is_send_week);
    }

    /** @test */
    public function success_only_required_field()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = self::data();

        $this->postJson(route('api.report.create'), $data)
            ->assertJson([
                'data' => [
                    'status' => ReportStatus::CREATED,
                    'user' => [
                        'id' => $user->id,
                    ],
                    'dealer' => [
                        "id" => $user->dealer->id,
                    ],
                    'machine' => [],
                    'clients' => [
                        'john_dear_client' => [],
                        'report_client' => []
                    ],
                    'location' => null,
                    'images' => [],
                    'comment' => null,
                    'salesman_name' => null,
                    'assignment' => null,
                    'result' => null,
                    'client_comment' => null,
                    'client_email' => null,
                    'fill_table_date' => '',
                    'verify' => null,
                    'video' => null,
                    'features' => [],
                    'planned_at' => null,
                ],
            ])
        ;
    }

    /** @test */
    public function success_with_planned_data()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = self::data();
        $data['planned_at'] = 1648981380000; // 2022-04-03 13:23:00

        $res = $this->postJson(route('api.report.create'), $data)
            ->assertJson([
                'data' => [
                    'status' => ReportStatus::CREATED,
                    'planned_at' => $data['planned_at'],
                ],
            ])
        ;

        $report = Report::query()->where('id', $res->json('data.id'))->first();

        $this->assertEquals($report->pushData->planned_at, '2022-04-03 13:23:00');
        $this->assertNull($report->pushData->prev_planned_at);
        $this->assertFalse($report->pushData->is_send_start_day);
        $this->assertFalse($report->pushData->is_send_end_day);
        $this->assertFalse($report->pushData->is_send_week);
    }

    /** @test */
    public function fail_not_ps()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = self::data();

        $this->postJson(route('api.report.create'), $data)
            ->assertJson($this->structureErrorResponse('This action is unauthorized.'))
        ;
    }

    /** @test */
    public function fail_wrong_md_if_exsist_eq()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $user->load(['dealer.nationality']);
        $this->loginAsUser($user);
        // 130
        $modelDescriptions = ModelDescription::query()
            ->with(['product.sizeParameter',])
            ->where('id', 130)
            ->first();

        $eg = EquipmentGroup::query()->where('jd_id', '!=', $modelDescriptions->eg_jd_id)->first();

        $modelDescriptions_2 = ModelDescription::query()
            ->where('id', '!=', $modelDescriptions->id)
            ->first();

        $modelDescriptions_3 = ModelDescription::query()
            ->where('id', '!=', $modelDescriptions->id)
            ->where('id', '!=', $modelDescriptions_2->id)
            ->first();

        $man = Manufacturer::query()->first();
        $man_2 = Manufacturer::query()->where('id', '!=', $man->id)->first();

        $data = self::data() + self::fullData();

        $data['machines'][] = [
            'manufacturer_id' => $man->id,
            'model_description_id' => $modelDescriptions->id,
            'equipment_group_id' => $eg->id,
            'header_model_id' => $modelDescriptions_2->id,
            'header_brand_id' => $man->id,
            'serial_number_header' => '7879678KJHG',
            'machine_serial_number' => '007879678KJHG',
            'trailed_equipment_type' => '1007879678KJHG',
            'trailer_model' => '2007879678KJHG',
            'sub_manufacturer_id' => $man_2->id,
            'sub_model_description_id' => $modelDescriptions_3->id,
            'sub_equipment_group_id' => $modelDescriptions_3->equipmentGroup->id,
            'sub_machine_serial_number' => '0001',
        ];

        $this->postJson(route('api.report.create'), $data)
            ->assertJson($this->structureErrorResponse([
                "This Equipment group [{$eg->id}] does not contain this Model Description [{$modelDescriptions->id}]"
            ]))
        ;
    }

    /** @test */
    public function fail_exist_md_and_not_exsist_eq()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $user->load(['dealer.nationality']);
        $this->loginAsUser($user);
        // 130
        $modelDescriptions = ModelDescription::query()
            ->with(['product.sizeParameter',])
            ->where('id', 130)
            ->first();

        $eg = EquipmentGroup::query()->where('jd_id', '!=', $modelDescriptions->eg_jd_id)->first();

        $modelDescriptions_2 = ModelDescription::query()
            ->where('id', '!=', $modelDescriptions->id)
            ->first();

        $modelDescriptions_3 = ModelDescription::query()
            ->where('id', '!=', $modelDescriptions->id)
            ->where('id', '!=', $modelDescriptions_2->id)
            ->first();

        $man = Manufacturer::query()->first();
        $man_2 = Manufacturer::query()->where('id', '!=', $man->id)->first();

        $data = self::data() + self::fullData();

        $data['machines'][] = [
            'manufacturer_id' => $man->id,
            'model_description_id' => $modelDescriptions->id,
            'equipment_group_id' => null,
            'header_model_id' => $modelDescriptions_2->id,
            'header_brand_id' => $man->id,
            'serial_number_header' => '7879678KJHG',
            'machine_serial_number' => '007879678KJHG',
            'trailed_equipment_type' => '1007879678KJHG',
            'trailer_model' => '2007879678KJHG',
            'sub_manufacturer_id' => $man_2->id,
            'sub_model_description_id' => $modelDescriptions_3->id,
            'sub_equipment_group_id' => $modelDescriptions_3->equipmentGroup->id,
            'sub_machine_serial_number' => '0001',
        ];

        $this->postJson(route('api.report.create'), $data)
            ->assertJson($this->structureErrorResponse([
                "This Equipment group [] does not contain this Model Description [{$modelDescriptions->id}]"
            ]))
        ;
    }

    public static function data(): array
    {
        return [
            'status' => ReportStatus::CREATED
        ];
    }

    public static function fullData(): array
    {
        return [
            'salesman_name' => 'some salesman name',
            'assignment' => 'some assignment',
            'result' => 'some result',
            'client_comment' => 'some client comment',
            'client_email' => 'some client email',
            'location' => [
                'location_lat' => '45.909000',
                'location_long' => '-5.909000',
                'location_country' => 'Poland',
                'location_city' => 'Poznani',
                'location_region' => 'Riga region',
                'location_zipcode' => '73000',
                'location_street' => 'st. Freedom',
                'location_district' => 'district Peace',
            ]
        ];
    }
}


