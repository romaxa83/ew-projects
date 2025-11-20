<?php

namespace Tests\Feature\Api\Report;

use App\Helpers\DateFormat;
use App\Models\Image;
use App\Models\JD\Client;
use App\Models\JD\Dealer;
use App\Models\JD\Manufacturer;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ClientType;
use App\Type\ModelDescription as MDType;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ShowTest extends TestCase
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
        /**
         * 2) протестировать модель пользователя в частности роль
        */

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $dealer Dealer */
        $dealer = Dealer::query()->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setDealer($dealer)
            ->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $md ModelDescription */
        $md = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();
        $md->product->update(['type' => MDType::TYPE_ONE]);
        $md_2 = ModelDescription::query()->where('id', '!=', $md->id)->first();

        $man = Manufacturer::query()->first();

        list($val_1, $val_2, $val_3) = ['val_1', 'val_2', 'val_3'];
        $feature_1 = $this->featureBuilder->setValues($val_1, $val_2)
            ->withTranslation()->setEgIds($md_2->equipmentGroup->id)->create();
        $feature_2 = $this->featureBuilder->withTranslation()->setEgIds($md_2->equipmentGroup->id)->create();

        $country = 'UK';
        $date = Carbon::now();

        $client = Client::query()->first();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setCountry($country)
            ->setUser($user)
            ->setMachineData([
                'manufacturer_id' => $man->id,
                'model_description_id' => $md->id,
                'equipment_group_id' => $md->equipmentGroup->id,
                'header_model_id' => $md->id,
                'header_brand_id' => $man->id,
                'sub_manufacturer_id' => $man->id,
                'sub_model_description_id' => $md->id,
                'sub_equipment_group_id' => $md->equipmentGroup->id,
            ])
            ->setFeatures([
                ["id" => $feature_1->id, "is_sub" => true, "group" => [
                    [
                        'id' => $md_2->id,
                        'name' => $md_2->name,
                        "choiceId" => $feature_1->values[0]->id
                    ]
                ]],
                ["id" => $feature_2->id, "is_sub" =>  false, "group" => [
                    [
                        'id' => $md_2->id,
                        'name' => $md_2->name,
                        "value" => $val_3,
                    ]
                ]]
            ])
            ->setClientJD($client, [
                'quantity_machine' => 30,
                'model_description_id' => $md_2->id,
                'type' => ClientType::TYPE_POTENTIAL,
            ])
            ->setClientCustom([
                'customer_id' => 't657'
            ],[
                'quantity_machine' => 35,
                'model_description_id' => $md_2->id,
                'type' => ClientType::TYPE_POTENTIAL,
            ])
            ->setPushData([
                'planned_at' => $date,
            ])
            ->create();

        $this->getJson(route('api.report.show', [
            'report' => $report
        ]),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson(['data' =>
                [
                    'id' => $report->id,
                    'title' => $report->title,
                    'status' => ReportStatus::IN_PROCESS,
                    'user' => [
                        'id' => $user->id,
                        'login' => $user->login,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'status' => $user->status,
                        'created' => DateFormat::front($user->created_at),
                        'updated' => DateFormat::front($user->updated_at),
                        'profile' => null,
                        'role' => [
                            'role' => $user->getRoleName(),
                            'alias' => $user->getRole()
                        ],
                        'lang' => $user->lang,
                        'country' => null,
                        'dealers' => [
                            [
                                'id' => $user->dealer->id,
                                'jd_id' => $user->dealer->jd_id,
                                'jd_jd_id' => $user->dealer->jd_jd_id,
                                'name' => $user->dealer->name,
                                'status' => $user->dealer->status,
                                'country' => [
                                    'id' => $user->dealer->nationality->id,
                                    'name' => $user->dealer->nationality->name,
                                    'alias' => $user->dealer->nationality->alias,
                                ],
                                'created' => DateFormat::front($user->dealer->created_at),
                                'updated' => DateFormat::front($user->dealer->updated_at),
                                'users' => []
                            ]
                        ],
                        'egs' => [],
                    ],
                    'dealer' => [
                        'id' => $user->dealer->id,
                        'jd_id' => $user->dealer->jd_id,
                        'jd_jd_id' => $user->dealer->jd_jd_id,
                        'name' => $user->dealer->name,
                        'status' => $user->dealer->status,
                        'country' => [
                            'id' => $user->dealer->nationality->id,
                            'name' => $user->dealer->nationality->name,
                            'alias' => $user->dealer->nationality->alias,
                        ],
                        'created' => DateFormat::front($user->dealer->created_at),
                        'updated' => DateFormat::front($user->dealer->updated_at),
                        'users' => []
                    ],
                    'machine' => [
                        [
                            'id' => $report->reportMachines[0]->id,
                            'manufacturer' => [
                                "id" => $man->id,
                                "name" => $man->name,
                            ],
                            'equipment_group' => [
                                "id" => $md->equipmentGroup->id,
                                "jd_id" => $md->equipmentGroup->jd_id,
                                "name" => $md->equipmentGroup->name,
                                "created" => DateFormat::front($md->equipmentGroup->created_at),
                                "updated" => DateFormat::front($md->equipmentGroup->updated_at),
                                "egs" => null
                            ],
                            'model_description' => [
                                "id" => $md->id,
                                "jd_id" => $md->jd_id,
                                "eg_jd_id" => $md->eg_jd_id,
                                "name" => $md->name,
                                "status" => $md->status,
                                "created" => DateFormat::front($md->created_at),
                                "updated" => DateFormat::front($md->updated_at),
                                "size" => $md->product->size_name,
                                "size_parameter" => $md->product->sizeParameter->name,
                                "type" => $md->product->type
                            ],
                            'trailed_equipment_type' => $report->reportMachines->first()->trailed_equipment_type,
                            'trailer_model' => $report->reportMachines->first()->trailer_model,
                            'header_brand' => [
                                "id" => $man->id,
                                "name" => $man->name,
                            ],
                            'header_model' => [
                                "id" => $md->id,
                                "name" => $md->name,
                            ],
                            'serial_number_header' => $report->reportMachines->first()->serial_number_header,
                            'machine_serial_number' => $report->reportMachines->first()->machine_serial_number,
                            'sub' => [
                                'machine_serial_number' => $report->reportMachines->first()->sub_machine_serial_number,
                                'manufacturer' => [
                                    "id" => $man->id,
                                    "name" => $man->name,
                                ],
                                'equipment_group' => [
                                    "id" => $md->equipmentGroup->id,
                                    "name" => $md->equipmentGroup->name,
                                ],
                                'model_description' => [
                                    "id" => $md->id,
                                    "name" => $md->name,
                                ],
                            ],
                        ]
                    ],
                    'clients' => [
                        "john_dear_client" => [
                            [
                                "id" => $client->id,
                                "jd_id" => $client->jd_id,
                                "customer_id" => $client->customer_id,
                                "customer_first_name" => $client->customer_first_name,
                                "customer_last_name" => $client->customer_last_name,
                                "customer_second_name" => $client->customer_second_name,
                                "company_name" => $client->company_name,
                                "phone" => $client->phone,
                                "status" => $client->status,
                                "type" => ClientType::TYPE_POTENTIAL,
                                "model_description" => [
                                    "id" => $md_2->id,
                                    "jd_id" => $md_2->jd_id,
                                    "name" => $md_2->name,
                                ],
                                "quantity_machine" => 30,
                                "region" => [
                                    "id" => $client->region->id,
                                    "name" => $client->region->name,
                                ]
                            ]
                        ],
                        "report_client" => [
                            [
                                "id" => $report->reportClients[0]->id,
                                "customer_id" => "t657",
                                "customer_first_name" => $report->reportClients[0]->customer_first_name,
                                "customer_last_name" => $report->reportClients[0]->customer_last_name,
                                "company_name" => $report->reportClients[0]->company_name,
                                "phone" => $report->reportClients[0]->phone,
                                "status" => $report->reportClients[0]->status,
                                "comment" => $report->reportClients[0]->comment,
                                "quantity_machine" => 35,
                                "type" => ClientType::TYPE_POTENTIAL,
                                "model_description" => [
                                    "id" => $md_2->id,
                                    "jd_id" => $md_2->jd_id,
                                    "eg_jd_id" => $md_2->eg_jd_id,
                                    "name" => $md_2->name,
                                ]
                            ]
                        ],
                    ],
                    'location' => [
                        'lat' => $report->location->lat,
                        'long' => $report->location->long,
                        'country' => $report->location->country,
                        'city' => $report->location->city,
                        'region' => $report->location->region,
                        'zipcode' => $report->location->zipcode,
                        'street' => $report->location->street,
                    ],
                    'images' => [],
                    'comment' => null,
                    'salesman_name' => $report->salesman_name,
                    'assignment' => $report->assignment,
                    'result' => $report->result,
                    'client_comment' => $report->client_comment,
                    'client_email' => $report->client_email,
                    'created' => DateFormat::front($report->created_at),
                    'updated' => DateFormat::front($report->updated_at),
                    'fill_table_date' => DateFormat::front($report->fill_table_date),
                    'verify' => false,
                    'video' => null,
                    'features' => [
                        [
                            'id' => $feature_1->id,
                            'name' => $feature_1->current->name,
                            'unit' => $feature_1->current->unit,
                            'type' => $feature_1->type,
                            'type_field' => $feature_1->type_field_for_front,
                            "is_sub" => true,
                            "group" => [
                                [
                                    "id" => $md_2->id,
                                    "name" => $md_2->name,
                                    "value" => $feature_1->values[0]->current->name,
                                    "choiceId" => $feature_1->values[0]->id
                                ]
                            ]
                        ],
                        [
                            'id' => $feature_2->id,
                            'name' => $feature_2->current->name,
                            'unit' => $feature_2->current->unit,
                            'type' => $feature_2->type,
                            'type_field' => $feature_2->type_field_for_front,
                            "is_sub" => false,
                            "group" => [
                                [
                                    "id" => $md_2->id,
                                    "name" => $md_2->name,
                                    "value" => $val_3,
                                    "choiceId" => null
                                ]
                            ]
                        ]
                    ],
//                    'planned_at' => $date->timestamp * 1000,
                ]
            ])
        ;
    }

    /** @test */
    public function success_view_as_admin()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)->create();

        /** @var $md ModelDescription */
        $md = ModelDescription::query()
            ->first();

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->setModelDescription($md)
            ->create();

        $this->getJson(route('api.report.show', [
            'report' => $report
        ]))
            ->assertJson(['data' => [
                'id' => $report->id
            ]])
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();

        /** @var $md ModelDescription */
        $md = ModelDescription::query()
            ->first();

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->setModelDescription($md)
            ->create();

        $this->getJson(route('api.report.show', ['report' => $report]))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
