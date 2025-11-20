<?php

namespace Tests\Unit\Export;

use App\Exports\ReportExport;
use App\Helpers\DateFormat;
use App\Models\JD\Client;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\Manufacturer;
use App\Models\JD\ModelDescription;
use App\Models\Report\Feature\Feature;
use App\Models\Translate;
use App\Repositories\Report\ReportRepository;
use App\Type\ClientType;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Tests\Builder\TranslationBuilder;
use Tests\TestCase;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;

class ReportExportTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;
    protected $featureBuilder;
    protected $translationBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->translationBuilder = resolve(TranslationBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        list($en, $ru) = ['en', 'ru'];
        $dealer = Dealer::query()->first();

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id]
        ])->first();
        $md_3 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['id', '!=', $md_2->id]
        ])->first();

        $man_1 = Manufacturer::query()->first();
        $man_2 = Manufacturer::query()->where([
            ['id', '!=', $man_1->id]
        ])->first();

        $client = Client::query()->first();

        $user = $this->userBuilder
            ->withProfile()
            ->setDealer($dealer)
            ->setLang($en)
            ->create();

        // FEATURES
        list($val_1, $val_2, $val_3, $val_4) = ['val_1', 'val_2', 'val_3', 'val_4'];
        $feature_1 = $this->featureBuilder->setValues($val_1, $val_2)
            ->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();
        $feature_2 = $this->featureBuilder->setValues($val_3, $val_2)
            ->withTranslation()->setType(Feature::TYPE_MACHINE)->setEgIds($md_1->equipmentGroup->id)->create();

        $feature_3 = $this->featureBuilder->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();
        $feature_4 = $this->featureBuilder->withTranslation()->setType(Feature::TYPE_MACHINE)->setEgIds($md_1->equipmentGroup->id)->create();

        $clientJDPivot_1 = [
            'quantity_machine' => 31,
            'model_description_id' => $md_1->id,
            'type' => ClientType::TYPE_POTENTIAL,
        ];

        $reportMachine_1 = [
            'manufacturer_id' => $man_1->id,
            'model_description_id' => $md_1->id,
            'equipment_group_id' => $md_1->equipmentGroup->id,
            'header_model_id' => $md_2->id,
            'header_brand_id' => $man_2->id,
            'machine_serial_number' => "qwerty123",
            'serial_number_header' => "qwerty1234",
            'trailed_equipment_type' => "qwerty12345",
            'trailer_model' => "qwerty123456",
            'sub_manufacturer_id' => $man_2->id,
            'sub_model_description_id' => $md_3->id,
            'sub_equipment_group_id' => $md_3->equipmentGroup->id,
            'sub_machine_serial_number' => "qwerty1234567",
        ];
        $reportMachine_2 = [
            'manufacturer_id' => $man_2->id,
            'model_description_id' => $md_1->id,
            'equipment_group_id' => $md_1->equipmentGroup->id,
            'header_model_id' => $md_2->id,
            'header_brand_id' => $man_1->id,
            'machine_serial_number' => "qwerty",
            'serial_number_header' => "qwerty1",
            'trailed_equipment_type' => "qwerty12",
            'trailer_model' => "qwerty123",
            'sub_manufacturer_id' => $man_1->id,
            'sub_model_description_id' => $md_3->id,
            'sub_equipment_group_id' => $md_3->equipmentGroup->id,
            'sub_machine_serial_number' => "qwerty1234",
        ];

        $location = [
            'country' => "UK",
            'city' => "City",
            'region' => "Region",
            'street' => "Street",
            'lat' => '56.009',
            'long' => '-56.009',
            'zipcode' => '73000',
        ];

        $customClient = [
            'customer_id' => 't657',
            'customer_first_name' => 'custom first name',
            'customer_last_name' => 'custom last name',
            'phone' => '1111111111',
            'company_name' => 'custom company name'
        ];

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->setTitle("some_title_1")
            ->setLocationData($location)
            ->setClientJD($client, $clientJDPivot_1)
            ->setClientCustom($customClient,[
                'quantity_machine' => 35,
                'model_description_id' => $md_1->id,
                'type' => ClientType::TYPE_COMPETITOR,
            ])
            ->setMachineData([
                $reportMachine_1,
                $reportMachine_2,
            ])
            ->setFeatures([
                ["id" => $feature_1->id, "is_sub" => true, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "choiceId" => $feature_1->values[0]->id
                    ]
                ]],
                ["id" => $feature_2->id, "is_sub" => true, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "choiceId" => $feature_2->values[0]->id
                    ]
                ]],
                ["id" => $feature_3->id, "is_sub" =>  false, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "value" => $val_4,
                    ]
                ]],
                ["id" => $feature_4->id, "is_sub" =>  false, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "value" => 4,
                    ]
                ]]
            ])
            ->create();

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->setTitle("some_title_2")
            ->setLocationData([
                'country' => "UK",
                'city' => "City",
                'region' => "Region",
                'street' => "Street",
                'lat' => '56.009',
                'long' => '-56.009',
            ])
            ->setClientJD($client, [
                'quantity_machine' => 30,
                'model_description_id' => $md_1->id,
                'type' => ClientType::TYPE_POTENTIAL,
            ])
            ->setClientCustom($customClient,[
                'quantity_machine' => 35,
                'model_description_id' => $md_1->id,
                'type' => ClientType::TYPE_COMPETITOR,
            ])
            ->setMachineData([
                'manufacturer_id' => $man_1->id,
                'model_description_id' => $md_1->id,
                'equipment_group_id' => $md_1->equipmentGroup->id,
                'header_model_id' => $md_1->id,
                'header_brand_id' => $man_2->id,
                'machine_serial_number' => "qwerty123",
                'serial_number_header' => "qwerty1234",
                'trailed_equipment_type' => "qwerty12345",
                'trailer_model' => "qwerty123456",
                'sub_manufacturer_id' => $man_1->id,
                'sub_model_description_id' => $md_1->id,
                'sub_equipment_group_id' => $md_1->equipmentGroup->id,
                'sub_machine_serial_number' => "qwerty1234567",
            ])
            ->setFeatures([
                ["id" => $feature_1->id, "is_sub" => true, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "choiceId" => $feature_1->values[0]->id
                    ]
                ]],
                ["id" => $feature_3->id, "is_sub" =>  false, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "value" => $val_4,
                    ]
                ]]
            ])
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->map($report);

        $this->assertCount(2, $data);
        $this->assertCount(32, data_get($data, '0'));
        $this->assertCount(20, data_get($data, '1'));
        $this->assertEquals($report->id, data_get($data, '0.0'));
        $this->assertEquals($report->title, data_get($data, '0.1'));
        $this->assertEquals($dealer->name, data_get($data, '0.2'));
        $this->assertEquals($dealer->country, data_get($data, '0.3'));
        $this->assertEquals($report->salesman_name, data_get($data, '0.4'));
        $this->assertEquals(
            $report->clients[0]->customer_first_name .' '. $report->clients[0]->customer_last_name.' '.$report->clients[0]->customer_second_name,
            data_get($data, '0.5')
        );
        $this->assertEquals($client->company_name, data_get($data, '0.6'));
        $this->assertEquals("'". str_replace('+', '', $client->phone), data_get($data, '0.7'));
        $this->assertEquals("potencial", data_get($data, '0.8'));

        $this->assertEquals($md_1->name, data_get($data, '0.9'));
        $this->assertEquals(data_get($clientJDPivot_1, 'quantity_machine'), data_get($data, '0.10'));
        $this->assertEquals($man_1->name, data_get($data, '0.11'));
        $this->assertEquals($md_1->equipmentGroup->name, data_get($data, '0.12'));
        $this->assertEquals($md_1->name, data_get($data, '0.13'));
        $this->assertEquals(data_get($reportMachine_1, 'machine_serial_number'), data_get($data, '0.14'));
        $this->assertEquals(data_get($reportMachine_1, 'trailer_model'), data_get($data, '0.15'));
        $this->assertEquals($man_2->name, data_get($data, '0.16'));
        $this->assertEquals($md_3->equipmentGroup->name, data_get($data, '0.17'));
        $this->assertEquals($md_3->name, data_get($data, '0.18'));
        $this->assertEquals(data_get($reportMachine_1, 'sub_machine_serial_number'), data_get($data, '0.19'));
        $this->assertEquals($user->profile->first_name.' '.$user->profile->last_name, data_get($data, '0.20'));
        $this->assertEquals($user->login, data_get($data, '0.21'));
        $this->assertEquals($user->email, data_get($data, '0.22'));
        $this->assertEquals("'". str_replace('+', '', $user->phone), data_get($data, '0.23'));
        $this->assertEquals($user->profile->country, data_get($data, '0.24'));
        $this->assertEquals(DateFormat::front($report->created_at), data_get($data, '0.25'));
        $this->assertEquals(
            data_get($location, 'country').', '.data_get($location, 'city').', '.data_get($location, 'region').', '.data_get($location, 'street').', '.data_get($location, 'zipcode'),
            data_get($data, '0.26')
        );
        $this->assertEquals($report->assignment, data_get($data, '0.27'));
        $this->assertEquals($report->result, data_get($data, '0.28'));
        $this->assertEquals($report->client_comment, data_get($data, '0.29'));
        // 2
        $this->assertNull(data_get($data, '1.0'));
        $this->assertNull(data_get($data, '1.1'));
        $this->assertNull(data_get($data, '1.2'));
        $this->assertNull(data_get($data, '1.3'));
        $this->assertNull(data_get($data, '1.4'));
        $this->assertEquals(
            data_get($customClient, 'customer_first_name').' '.data_get($customClient, 'customer_last_name'). ' ',
            data_get($data, '1.5')
        );
        $this->assertEquals(data_get($customClient, 'company_name'), data_get($data, '1.6'));
        $this->assertEquals(data_get($customClient, 'phone'), data_get($data, '1.7'));
        $this->assertEquals('competitor', data_get($data, '1.8'));
        $this->assertNull(data_get($data, '1.9'));
        $this->assertNull(data_get($data, '1.10'));
        $this->assertEquals($man_2->name, data_get($data, '1.11'));
        $this->assertEquals($md_1->equipmentGroup->name, data_get($data, '1.12'));
        $this->assertEquals($md_1->name, data_get($data, '1.13'));
        $this->assertEquals(data_get($reportMachine_2, 'machine_serial_number'), data_get($data, '1.14'));
        $this->assertEquals(data_get($reportMachine_2, 'trailer_model'), data_get($data, '1.15'));
        $this->assertEquals($man_1->name, data_get($data, '1.16'));
        $this->assertEquals($md_3->equipmentGroup->name, data_get($data, '1.17'));
        $this->assertEquals($md_3->name, data_get($data, '1.18'));
        $this->assertEquals(data_get($reportMachine_2, 'sub_machine_serial_number'), data_get($data, '1.19'));
    }

    /** @test */
    public function success_empty_report(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->map($report);

//        dd($data);
        $this->assertCount(1, $data);
        $this->assertEquals($report->id, data_get($data, '0.0'));
        $this->assertNull(data_get($data, '0.1'));
        $this->assertNull(data_get($data, '0.2'));
        $this->assertNull(data_get($data, '0.3'));
        $this->assertEquals($report->salesman_name, data_get($data, '0.4'));
        $this->assertEquals('  ', data_get($data, '0.5'));
        $this->assertNull(data_get($data, '0.6'));
        $this->assertNull(data_get($data, '0.7'));
        $this->assertEquals('', data_get($data, '0.8'));
        $this->assertNull(data_get($data, '0.9'));
        $this->assertNull(data_get($data, '0.10'));
        $this->assertNull(data_get($data, '0.11'));
        $this->assertNull(data_get($data, '0.12'));
        $this->assertNull(data_get($data, '0.13'));
        $this->assertNull(data_get($data, '0.14'));
        $this->assertNull(data_get($data, '0.15'));
        $this->assertNull(data_get($data, '0.16'));
        $this->assertNull(data_get($data, '0.17'));
        $this->assertNull(data_get($data, '0.18'));
        $this->assertNull(data_get($data, '0.19'));
        $this->assertEquals(' ', data_get($data, '0.20'));
        $this->assertEquals($report->user->login, data_get($data, '0.21'));
        $this->assertEquals($report->user->email, data_get($data, '0.22'));
        $this->assertEquals("'". str_replace('+', '', $report->user->phone), data_get($data, '0.23'));
        $this->assertNull(data_get($data, '0.24'));
        $this->assertEquals(DateFormat::front($report->created_at), data_get($data, '0.25'));
        $this->assertEquals(", , , , ", data_get($data, '0.26'));
        $this->assertEquals($report->assignment, data_get($data, '0.27'));
        $this->assertEquals($report->result, data_get($data, '0.28'));
        $this->assertEquals($report->client_comment, data_get($data, '0.29'));
    }

    /** @test */
    public function check_start_sell(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->startCell();

        $this->assertEquals("A2", $data);
    }

    /** @test */
    public function check_acd(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->acd();

        $acd = ['A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I',
            'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S',
            'T', 'U', 'V', 'W', 'X',
            'Y', 'Z',
        ];

        $this->assertEquals($acd, $data);
    }

    /** @test */
    public function check_excel_column(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->getExcelColumn(1);
        $this->assertEquals("A", $data);

        $data = $model->getExcelColumn(5);
        $this->assertEquals("E", $data);

        $data = $model->getExcelColumn(26);
        $this->assertEquals("Z", $data);

        $data = $model->getExcelColumn(27);
        $this->assertEquals("AA", $data);

        $data = $model->getExcelColumn(35);
        $this->assertEquals("AI", $data);

        $data = $model->getExcelColumn(60);
        $this->assertEquals("BH", $data);

        $data = $model->getExcelColumn(160);
        $this->assertEquals("FD", $data);
    }

    /** @test */
    public function check_drawings(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->drawings();

        $this->assertTrue($data instanceof Drawing);

        $this->assertEquals("Logo", $data->getName());
        $this->assertEquals('This is my logo', $data->getDescription());
        $this->assertEquals(public_path('/static/logo.png'), $data->getPath());
        $this->assertEquals(20, $data->getHeight());
        $this->assertEquals('B1', $data->getCoordinates());
    }

    /** @test */
    public function check_register_events(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->registerEvents();

        $this->assertTrue($data["Maatwebsite\Excel\Events\AfterSheet"] instanceof \Closure);
    }

    /** @test */
    public function check_headings_exist_translate(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        list($en, $ru) = ['en', 'ru'];

        $tran_1 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.id')->setLang($en)->create();
        $tran_2 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.title')->setLang($en)->create();
        $tran_3 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.dealer_name')->setLang($en)->create();
        $tran_4 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.dealer_company')->setLang($en)->create();
        $tran_5 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.salesman_name')->setLang($en)->create();
        $tran_6 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.client_name')->setLang($en)->create();
        $tran_7 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.client_company')->setLang($en)->create();
        $tran_8 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.client_phone')->setLang($en)->create();
        $tran_9 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.client_status')->setLang($en)->create();
        $tran_10 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.client_machine')->setLang($en)->create();
        $tran_11 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.client_quantity_machine')->setLang($en)->create();
        $tran_12 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.manufacturer')->setLang($en)->create();
        $tran_13 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.equipment_group')->setLang($en)->create();
        $tran_14 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.model_description')->setLang($en)->create();
        $tran_15 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.machine_serial_number')->setLang($en)->create();
        $tran_16 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.trailer_model')->setLang($en)->create();
        $tran_17 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.sub_manufacture')->setLang($en)->create();
        $tran_18 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.sub_equipment_group')->setLang($en)->create();
        $tran_19 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.sub_model_description')->setLang($en)->create();
        $tran_20 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.sub_serial_number')->setLang($en)->create();
        $tran_21 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.ps_name')->setLang($en)->create();
        $tran_22 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.ps_login')->setLang($en)->create();
        $tran_23 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.ps_email')->setLang($en)->create();
        $tran_24 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.ps_phone')->setLang($en)->create();
        $tran_25 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.ps_country')->setLang($en)->create();
        $tran_26 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.created')->setLang($en)->create();
        $tran_27 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.location')->setLang($en)->create();
        $tran_28 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.assignment')->setLang($en)->create();
        $tran_29 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.result')->setLang($en)->create();
        $tran_30 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.client_comment')->setLang($en)->create();
        $tran_31 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.head_sub_model')->setLang($en)->create();
        $tran_32 = $this->translationBuilder->setGroup(Translate::GROUP_EXCEL)->setAlias('file.head_model')->setLang($en)->create();

        $eg_1 = EquipmentGroup::query()->first();

        $feature_1 = $this->featureBuilder->withTranslation()->setPosition(2)
            ->setType(Feature::TYPE_GROUND)->setEgIds($eg_1->id)->create();
        $feature_2 = $this->featureBuilder->withTranslation()->setPosition(1)
            ->setType(Feature::TYPE_GROUND)->setEgIds($eg_1->id)->create();
        $feature_3 = $this->featureBuilder->withTranslation()->setPosition(3)
            ->setType(Feature::TYPE_MACHINE)->setEgIds($eg_1->id)->create();
        $feature_4 = $this->featureBuilder->withTranslation()->setPosition(4)
            ->setType(Feature::TYPE_MACHINE)->setEgIds($eg_1->id)->create();

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->headings();

        $this->assertCount(38, $data);

        $this->assertEquals(data_get($data, '0'), $tran_1->text);
        $this->assertEquals(data_get($data, '1'), $tran_2->text);
        $this->assertEquals(data_get($data, '2'), $tran_3->text);
        $this->assertEquals(data_get($data, '3'), $tran_4->text);
        $this->assertEquals(data_get($data, '4'), $tran_5->text);
        $this->assertEquals(data_get($data, '5'), $tran_6->text);
        $this->assertEquals(data_get($data, '6'), $tran_7->text);
        $this->assertEquals(data_get($data, '7'), $tran_8->text);
        $this->assertEquals(data_get($data, '8'), $tran_9->text);
        $this->assertEquals(data_get($data, '9'), $tran_10->text);
        $this->assertEquals(data_get($data, '10'), $tran_11->text);
        $this->assertEquals(data_get($data, '11'), $tran_12->text);
        $this->assertEquals(data_get($data, '12'), $tran_13->text);
        $this->assertEquals(data_get($data, '13'), $tran_14->text);
        $this->assertEquals(data_get($data, '14'), $tran_15->text);
        $this->assertEquals(data_get($data, '15'), $tran_16->text);
        $this->assertEquals(data_get($data, '16'), $tran_17->text);
        $this->assertEquals(data_get($data, '17'), $tran_18->text);
        $this->assertEquals(data_get($data, '18'), $tran_19->text);
        $this->assertEquals(data_get($data, '19'), $tran_20->text);
        $this->assertEquals(data_get($data, '20'), $tran_21->text);
        $this->assertEquals(data_get($data, '21'), $tran_22->text);
        $this->assertEquals(data_get($data, '22'), $tran_23->text);
        $this->assertEquals(data_get($data, '23'), $tran_24->text);
        $this->assertEquals(data_get($data, '24'), $tran_25->text);
        $this->assertEquals(data_get($data, '25'), $tran_26->text);
        $this->assertEquals(data_get($data, '26'), $tran_27->text);
        $this->assertEquals(data_get($data, '27'), $tran_28->text);
        $this->assertEquals(data_get($data, '28'), $tran_29->text);
        $this->assertEquals(data_get($data, '29'), $tran_30->text);

        $this->assertEquals(data_get($data, '30'), $feature_1->current->name.' ( '.$feature_1->current->unit.' )');
        $this->assertEquals(data_get($data, '31'), $feature_2->current->name.' ( '.$feature_2->current->unit.' )');

        $this->assertEquals(data_get($data, '32'), $tran_32->text);

        $this->assertEquals(data_get($data, '33'), $feature_4->current->name.' ( '.$feature_4->current->unit.' )');
        $this->assertEquals(data_get($data, '34'), $feature_3->current->name.' ( '.$feature_3->current->unit.' )');

        $this->assertEquals(data_get($data, '35'), $tran_31->text);

        $this->assertEquals(data_get($data, '36'), $feature_4->current->name.' ( '.$feature_4->current->unit.' )');
        $this->assertEquals(data_get($data, '37'), $feature_3->current->name.' ( '.$feature_3->current->unit.' )');
    }

    /** @test */
    public function check_headings_without_translate(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->headings();

        $this->assertCount(32, $data);

        foreach ($data as $item){
            $this->assertNull($item);
        }


    }

    /** @test */
    public function check_pretty_value(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $model = new ReportExport($reports);

        $data = $model->prettyValue(true);
        $this->assertEquals("Yes", $data);

        $data = $model->prettyValue(false);
        $this->assertEquals("No", $data);

        $data = $model->prettyValue('some string');
        $this->assertEquals('some string', $data);

        $data = $model->prettyValue(3);
        $this->assertEquals(3, $data);
    }

    /** @test */
    public function check_collection(): void
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();
        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();
        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $repo = app(ReportRepository::class);

        $reports = $repo->getAllReportForExcel([
            'user',
            'user.profile',
            'user.dealer',
            'user.dealer.tm',
            'clients',
            'clients.region',
            'reportClients',
            'location',
            'reportMachines',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'reportMachines.manufacturer',
            'features.feature',
            'features.value',
        ],
            [],[],[]
        );

        $this->assertCount(3, $reports);

        $model = new ReportExport($reports);

        $data = $model->collection();

        $this->assertEquals(md5($data), md5($reports));
    }
}



