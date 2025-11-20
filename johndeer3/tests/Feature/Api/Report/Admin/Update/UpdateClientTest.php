<?php

namespace Tests\Feature\Api\Report\Admin\Update;

use App\Models\JD\Client;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Type\ClientType;
use App\Type\ReportStatus;
use Tests\TestCase;
use Tests\Builder\UserBuilder;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Report\ReportBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
        $user = $this->userBuilder->create();
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

        // john_dear_client
        $data['clients']['john_dear_client'][] = [
            'client_id' => $client_2->id,
            'type' => ClientType::TYPE_COMPETITOR,
            'model_description_id' => $md_2->id,
            'quantity_machine' => 2
        ];
        // report client
        $data['clients']['report_client'][] = [
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

        $this->assertNotEquals($rep->clients->first()->id, data_get($data, 'clients.john_dear_client.0.client_id'));
        $this->assertNotEquals($rep->clients->first()->pivot->model_description_id, data_get($data, 'clients.john_dear_client.0.model_description_id'));
        $this->assertNotEquals($rep->clients->first()->pivot->type, data_get($data, 'clients.john_dear_client.0.type'));
        $this->assertNotEquals($rep->clients->first()->pivot->quantity_machine, data_get($data, 'clients.john_dear_client.0.quantity_machine'));

        $this->assertNotEquals($rep->reportClients->first()->customer_id, data_get($data, 'clients.report_client.0.customer_id'));
        $this->assertNotEquals($rep->reportClients->first()->customer_first_name, data_get($data, 'clients.report_client.0.customer_first_name'));
        $this->assertNotEquals($rep->reportClients->first()->customer_last_name, data_get($data, 'clients.report_client.0.customer_last_name'));
        $this->assertNotEquals($rep->reportClients->first()->company_name, data_get($data, 'clients.report_client.0.company_name'));
        $this->assertNotEquals($rep->reportClients->first()->phone, data_get($data, 'clients.report_client.0.customer_phone'));
        $this->assertNotEquals($rep->reportClients->first()->comment, data_get($data, 'clients.report_client.0.comment'));
        $this->assertNotEquals($rep->reportClients->first()->pivot->model_description_id, data_get($data, 'clients.report_client.0.model_description_id'));
        $this->assertNotEquals($rep->reportClients->first()->pivot->type, data_get($data, 'clients.report_client.0.type'));
        $this->assertNotEquals($rep->reportClients->first()->pivot->quantity_machine, data_get($data, 'clients.report_client.0.quantity_machine'));


        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureSuccessResponse([
                "id" => $rep->id,
                "clients" => [
                    "john_dear_client" => [
                        [
                            'id' => $rep->clients->first()->id,
                            'type' => $rep->clients->first()->pivot->type,
                            'quantity_machine' => data_get($data, "clients.john_dear_client.0.quantity_machine"),
                            'model_description' => [
                                "id" => $rep->clients->first()->pivot->model_description_id
                            ]
                        ]
                    ],
                    "report_client" => [
                        [
                            "customer_id" => $rep->reportClients->first()->customer_id,
                            "customer_first_name" => $rep->reportClients->first()->customer_first_name,
                            "customer_last_name" => $rep->reportClients->first()->customer_last_name,
                            "company_name" => $rep->reportClients->first()->company_name,
                            "phone" => $rep->reportClients->first()->phone,
                            "comment" => $rep->reportClients->first()->comment,
                            'quantity_machine' => data_get($data, "clients.report_client.0.quantity_machine"),
                            'type' => $rep->reportClients->first()->pivot->type,
                            'model_description' => [
                                "id" => $rep->reportClients->first()->pivot->model_description_id
                            ]
                        ]
                    ]
                ],
            ]))
        ;
    }

    /** @test */
    public function success_empty()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();

        $client_1 = Client::query()->first();

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

        // john_dear_client
        $data['clients']['john_dear_client'][] = [];
        // report client
        $data['clients']['report_client'][] = [];

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureSuccessResponse([
                "id" => $rep->id,
                "clients" => [
                    "john_dear_client" => [
                        [
                            'id' => $rep->clients->first()->id,
                            'type' => $rep->clients->first()->pivot->type,
                            'quantity_machine' => $rep->clients->first()->pivot->quantity_machine,
                            'model_description' => [
                                "id" => $rep->clients->first()->pivot->model_description_id
                            ]
                        ]
                    ],
                    "report_client" => [
                        [
                            "customer_id" => $rep->reportClients->first()->customer_id,
                            "customer_first_name" => $rep->reportClients->first()->customer_first_name,
                            "customer_last_name" => $rep->reportClients->first()->customer_last_name,
                            "company_name" => $rep->reportClients->first()->company_name,
                            "phone" => $rep->reportClients->first()->phone,
                            "comment" => $rep->reportClients->first()->comment,
                            'quantity_machine' => $rep->reportClients->first()->pivot->quantity_machine,
                            'type' => $rep->reportClients->first()->pivot->type,
                            'model_description' => [
                                "id" => $rep->reportClients->first()->pivot->model_description_id
                            ]
                        ]
                    ]
                ],
            ]))
        ;
    }
}
