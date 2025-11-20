<?php

namespace Tests\Feature\Api\Report\Update\Ps;

use App\Helpers\DateFormat;
use App\Helpers\ReportHelper;
use App\Models\JD\Client;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ClientType;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdateClientTest extends TestCase
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
            ['id', '!=', $md_1->id]
        ])->first();
        $md_3 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['id', '!=', $md_2->id]
        ])->first();

        $client_1 = Client::query()->first();
        $client_2 = Client::query()->where('id', '!=', $client_1->id)->first();

        // john_dear_client
        $data['clients'][] = [
            'client_id' => $client_2->id,
            'type' => ClientType::TYPE_COMPETITOR,
            'model_description_id' => $md_2->id,
            'quantity_machine' => 2
        ];
        // report client
        $data['clients'][] = [
            'customer_id' => 'customer_id_1',
            'customer_first_name' => 'customer_first_name_1',
            'customer_last_name' => 'customer_last_name_1',
            'company_name' => 'company_name_1',
            'customer_phone' => 'customer_phone_1',
            'comment' => 'comment_1',
            'model_description_id' => $md_3->id,
            'quantity_machine' => 1,
            'type' => ClientType::TYPE_COMPETITOR,
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setClientJD($client_1, [
                'quantity_machine' => 30,
                'model_description_id' => $md_1->id,
                'type' => ClientType::TYPE_POTENTIAL,
            ])
            ->setClientCustom([
                'customer_id' => 't657'
            ],[
                'quantity_machine' => 35,
                'model_description_id' => $md_1->id,
                'type' => ClientType::TYPE_POTENTIAL,
            ])
            ->setUser($user)
            ->create();

        $reportTitle = $rep->title;
        $this->assertCount(1, $rep->clients);
        $this->assertCount(1, $rep->reportClients);

        $this->assertNotEquals($rep->clients->first()->id, data_get($data, 'clients.0.client_id'));
        $this->assertNotEquals($rep->clients->first()->pivot->model_description_id, data_get($data, 'clients.0.model_description_id'));
        $this->assertNotEquals($rep->clients->first()->pivot->type, data_get($data, 'clients.0.type'));
        $this->assertNotEquals($rep->clients->first()->pivot->quantity_machine, data_get($data, 'clients.0.quantity_machine'));

        $this->assertNotEquals($rep->reportClients->first()->customer_id, data_get($data, 'clients.1.customer_id'));
        $this->assertNotEquals($rep->reportClients->first()->customer_first_name, data_get($data, 'clients.1.customer_first_name'));
        $this->assertNotEquals($rep->reportClients->first()->customer_last_name, data_get($data, 'clients.1.customer_last_name'));
        $this->assertNotEquals($rep->reportClients->first()->company_name, data_get($data, 'clients.1.company_name'));
        $this->assertNotEquals($rep->reportClients->first()->phone, data_get($data, 'clients.1.customer_phone'));
        $this->assertNotEquals($rep->reportClients->first()->comment, data_get($data, 'clients.1.comment'));
        $this->assertNotEquals($rep->reportClients->first()->pivot->model_description_id, data_get($data, 'clients.1.model_description_id'));
        $this->assertNotEquals($rep->reportClients->first()->pivot->type, data_get($data, 'clients.1.type'));
        $this->assertNotEquals($rep->reportClients->first()->pivot->quantity_machine, data_get($data, 'clients.1.quantity_machine'));

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "clients" => [
                        "john_dear_client" => [
                            [
                                'id' => data_get($data, 'clients.0.client_id'),
                                'type' => data_get($data, 'clients.0.type'),
                                'quantity_machine' => data_get($data, 'clients.0.quantity_machine'),
                                'model_description' => [
                                    "id" => data_get($data, 'clients.0.model_description_id')
                                ]
                            ]
                        ],
                        "report_client" => [
                            [
                                "customer_id" => data_get($data, 'clients.1.customer_id'),
                                "customer_first_name" => data_get($data, 'clients.1.customer_first_name'),
                                "customer_last_name" => data_get($data, 'clients.1.customer_last_name'),
                                "company_name" => data_get($data, 'clients.1.company_name'),
                                "phone" => data_get($data, 'clients.1.customer_phone'),
                                "comment" => data_get($data, 'clients.1.comment'),
                                'quantity_machine' => data_get($data, 'clients.1.quantity_machine'),
                                'type' => data_get($data, 'clients.1.type'),
                                'model_description' => [
                                    "id" => data_get($data, 'clients.1.model_description_id')
                                ]
                            ]
                        ]
                    ],
                ]
            ])
        ;

        $rep->refresh();

        $title = ReportHelper::prettyTitle($user->dealer->name .'_'.$client_2->company_name.'__'.DateFormat::forTitle($rep->created_at));

        $this->assertCount(1, $rep->clients);
        $this->assertCount(1, $rep->reportClients);

        $this->assertNotEquals($reportTitle, $title);
        $this->assertEquals($rep->title, $title);
    }

    /** @test */
    public function success_only_jd_client()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();

        $client_1 = Client::query()->first();
        $client_2 = Client::query()->where('id', '!=', $client_1->id)->first();

        // john_dear_client
        $data['clients'][] = [
            'client_id' => $client_2->id,
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setClientJD($client_1, [
                'quantity_machine' => 30,
                'model_description_id' => $md_1->id,
                'type' => ClientType::TYPE_POTENTIAL,
            ])
            ->setUser($user)
            ->create();

        $reportTitle = $rep->title;

        $this->assertNotEquals($rep->clients->first()->id, data_get($data, 'clients.0.client_id'));
        $this->assertEquals($rep->clients->first()->pivot->model_description_id, $md_1->id);
        $this->assertEquals($rep->clients->first()->pivot->type, ClientType::TYPE_POTENTIAL);
        $this->assertEquals($rep->clients->first()->pivot->quantity_machine, 30);

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "clients" => [
                        "john_dear_client" => [
                            [
                                'id' => data_get($data, 'clients.0.client_id'),
                                'type' => null,
                                'quantity_machine' => null,
                                'model_description' => null
                            ]
                        ],
                        "report_client" => []
                    ],
                ]
            ])
            ->assertJsonCount(0, 'data.clients.report_client')
        ;

        $rep->refresh();

        $title = ReportHelper::prettyTitle($user->dealer->name .'_'.$client_2->company_name.'__'.DateFormat::forTitle($rep->created_at));

        $this->assertNotEquals($reportTitle, $title);
        $this->assertEquals($rep->title, $title);
    }

    /** @test */
    public function success_only_custom_client()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id]
        ])->first();

        // report client
        $data['clients'][] = [
            'customer_id' => 'customer_id_1',
            'model_description_id' => $md_2->id,
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setClientCustom([
                'customer_id' => 't657'
            ],[
                'quantity_machine' => 35,
                'model_description_id' => $md_1->id,
                'type' => ClientType::TYPE_POTENTIAL,
            ])
            ->setUser($user)
            ->create();

        $reportTitle = $rep->title;

        $this->assertCount(1, $rep->reportClients);

        $this->assertNotNull($rep->reportClients->first()->customer_id);
        $this->assertNotNull($rep->reportClients->first()->customer_first_name);
        $this->assertNotNull($rep->reportClients->first()->customer_last_name);
        $this->assertNotNull($rep->reportClients->first()->company_name);
        $this->assertNotNull($rep->reportClients->first()->phone);
        $this->assertNotNull($rep->reportClients->first()->comment);
        $this->assertNotNull($rep->reportClients->first()->pivot->model_description_id);
        $this->assertNotNull($rep->reportClients->first()->pivot->type);
        $this->assertNotNull($rep->reportClients->first()->pivot->quantity_machine);

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "clients" => [
                        "report_client" => [
                            [
                                "customer_id" => data_get($data, 'clients.0.customer_id'),
                                "customer_first_name" => null,
                                "customer_last_name" => null,
                                "company_name" => null,
                                "phone" => null,
                                "comment" => null,
                                'quantity_machine' => null,
                                'type' => null,
                                'model_description' => [
                                    "id" => data_get($data, 'clients.0.model_description_id')
                                ]
                            ]
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(0, 'data.clients.john_dear_client')
        ;

        $rep->refresh();

        $title = ReportHelper::prettyTitle($user->dealer->name .'___'.DateFormat::forTitle($rep->created_at));

        $this->assertNotEquals($reportTitle, $title);
        $this->assertEquals($rep->title, $title);
    }
}
