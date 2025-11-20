<?php

namespace Tests\Unit\Resources\Custom;

use App\Models\Image;
use App\Models\JD\Client;
use App\Models\JD\Dealer;
use App\Models\JD\Manufacturer;
use App\Models\JD\ModelDescription;
use App\Models\Page\Page;
use App\Models\Report\Report;
use App\Models\Report\Video;
use App\Resources\Custom\CustomReportPdfResource;
use App\Type\ClientType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\PageBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\TranslationBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class CustomReportPdfResourceTest extends TestCase
{
    use DatabaseTransactions;

    protected $reportBuilder;
    protected $userBuilder;
    protected $pageBuilder;
    protected $featureBuilder;
    protected $translationBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->pageBuilder = resolve(PageBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->translationBuilder = resolve(TranslationBuilder::class);
    }

    /** @test */
    public function success_all_data_without_translates(): void
    {
        list($en, $ru) = ['en', 'ru'];
        $dealer = Dealer::query()->first();
        $md_1 = ModelDescription::query()->first();
        $man = Manufacturer::query()->first();
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
        $feature_3 = $this->featureBuilder->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();

        $report = $this->reportBuilder
            ->setUser($user)
            ->setTitle("some_title")
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
            ->setClientCustom([
                'customer_id' => 't657',
                'customer_first_name' => 'custom first name',
                'customer_last_name' => 'custom last name',
                'phone' => '1111111111',
                'company_name' => 'custom company name'
            ],[
                'quantity_machine' => 35,
                'model_description_id' => $md_1->id,
                'type' => ClientType::TYPE_COMPETITOR,
            ])
            ->setMachineData([
                'manufacturer_id' => $man->id,
                'model_description_id' => $md_1->id,
                'equipment_group_id' => $md_1->equipmentGroup->id,
                'header_model_id' => $md_1->id,
                'header_brand_id' => $man->id,
                'machine_serial_number' => "qwerty123",
                'serial_number_header' => "qwerty1234",
                'trailed_equipment_type' => "qwerty12345",
                'trailer_model' => "qwerty123456",
                'sub_manufacturer_id' => $man->id,
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

        $video = new Video();
        $video->report_id = $report->id;
        $video->url = env('APP_URL') . '/storage/'. 'some_path';
        $video->name = 'some_name';
        $video->save();

        $this->imagesSave($report->id);

        $disclaimer = $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)
            ->withTranslations($en, $ru)->create();

        $data =  resolve(CustomReportPdfResource::class)->fill($report);

        // report
        $this->assertEquals(data_get($data, 'title'), $report->title);
        $this->assertEquals(data_get($data, 'salesman_name'), $report->salesman_name);
        $this->assertEquals(data_get($data, 'assignment'), $report->assignment);
        $this->assertEquals(data_get($data, 'client_comment'), $report->client_comment);
        $this->assertEquals(data_get($data, 'demo_result'), $report->result);
        // user
        $this->assertEquals(data_get($data, 'user_full_name'), $user->profile->first_name .' '.$user->profile->last_name);
        $this->assertEquals(data_get($data, 'user_country'), $user->country?->name);
        $this->assertEquals(data_get($data, 'user_email'), $user->email);
        $this->assertEquals(data_get($data, 'user_phone'), $user->phone);
        // dealer
        $this->assertEquals(data_get($data, 'dealer_name'), $dealer->name);
        $this->assertEquals(data_get($data, 'dealer_country'), $dealer->country);
        $this->assertEquals(data_get($data, 'dealer_id'), $dealer->jd_jd_id);
        // disclaimer
        $this->assertEquals(data_get($data, 'disclaimer'), $disclaimer->translations->where('lang', $user->lang)->first()->text);
        // location
        $this->assertEquals(data_get($data, 'location'), "UK,Region,City,Street,");
        $this->assertEquals(data_get($data, 'location_lat'), '56.009');
        $this->assertEquals(data_get($data, 'location_long'),'-56.009');
        // clients
        $this->assertEquals(data_get($data, 'customers.0.company_name'), $client->company_name);
        $this->assertEquals(data_get($data, 'customers.0.first_name'), $client->customer_first_name);
        $this->assertEquals(data_get($data, 'customers.0.last_name'), $client->customer_last_name);
        $this->assertEquals(data_get($data, 'customers.0.phone'), $client->phone);
        $this->assertEquals(data_get($data, 'customers.0.product_name'), $md_1->name);
        $this->assertEquals(data_get($data, 'customers.0.quantity_machine'), 30);
        $this->assertEquals(data_get($data, 'customers.0.type'), 'potencial');
        $this->assertEquals(data_get($data, 'customers.1.company_name'), 'custom company name');
        $this->assertEquals(data_get($data, 'customers.1.first_name'), 'custom first name');
        $this->assertEquals(data_get($data, 'customers.1.last_name'), 'custom last name');
        $this->assertEquals(data_get($data, 'customers.1.phone'), '1111111111');
        $this->assertEquals(data_get($data, 'customers.1.product_name'), $md_1->name);
        $this->assertEquals(data_get($data, 'customers.1.quantity_machine'), 35);
        $this->assertEquals(data_get($data, 'customers.1.type'), 'competitor');
        // machine
        $this->assertEquals(data_get($data, 'machines.0.manufacturer'), $man->name);
        $this->assertEquals(data_get($data, 'machines.0.equipment_group'), $md_1->equipmentGroup->name);
        $this->assertEquals(data_get($data, 'machines.0.model_description'), $md_1->name);
        $this->assertEquals(data_get($data, 'machines.0.machine_serial_number'), 'qwerty123');
        $this->assertEquals(data_get($data, 'machines.0.machine_serial_number'), 'qwerty123');
        $this->assertEquals(data_get($data, 'machines.0.header_brand'), $man->name);
        $this->assertEquals(data_get($data, 'machines.0.header_model'), $md_1->name);
        $this->assertEquals(data_get($data, 'machines.0.serial_number_header'), "qwerty1234");
        $this->assertEquals(data_get($data, 'machines.0.trailer_model'), "qwerty123456");
        $this->assertEquals(data_get($data, 'machines.0.type'), "machine_with_trailer");
        $this->assertEquals(data_get($data, 'machines.0.sub_manufacturer'), $man->name);
        $this->assertEquals(data_get($data, 'machines.0.sub_equipment_group'), $md_1->equipmentGroup->name);
        $this->assertEquals(data_get($data, 'machines.0.sub_model_description'), $md_1->name);
        $this->assertEquals(data_get($data, 'machines.0.sub_machine_serial_number'), "qwerty1234567");
        $this->assertEquals($data['machines'][0]['model_description.size'], null);
        $this->assertEquals($data['machines'][0]['model_description.size_parameter'], null);
        $this->assertEquals($data['machines'][0]['model_description.type'], null);
        // features
        $this->assertEquals(data_get($data, 'features.0.id'), $feature_1->id);
        $this->assertEquals(data_get($data, 'features.0.name'), $feature_1->current->name);
        $this->assertEquals(data_get($data, 'features.0.unit'), $feature_1->current->unit);
        $this->assertEquals(data_get($data, 'features.0.type'), $feature_1->type);
        $this->assertEquals(data_get($data, 'features.0.type_field'), 0);
        $this->assertEquals(data_get($data, 'features.0.is_sub'), true);
        $this->assertEquals(data_get($data, 'features.0.group.0.id'), $md_1->id);
        $this->assertEquals(data_get($data, 'features.0.group.0.name'), $md_1->name);
        $this->assertEquals(data_get($data, 'features.0.group.0.value'), $val_1);
        $this->assertEquals(data_get($data, 'features.0.group.0.choiceId'), $feature_1->values[0]->id);
        $this->assertEquals(data_get($data, 'features.1.id'), $feature_3->id);
        $this->assertEquals(data_get($data, 'features.1.name'), $feature_3->current->name);
        $this->assertEquals(data_get($data, 'features.1.unit'), $feature_3->current->unit);
        $this->assertEquals(data_get($data, 'features.1.type'), $feature_3->type);
        $this->assertEquals(data_get($data, 'features.1.type_field'), 0);
        $this->assertEquals(data_get($data, 'features.1.is_sub'), false);
        $this->assertEquals(data_get($data, 'features.1.group.1.id'), $md_1->id);
        $this->assertEquals(data_get($data, 'features.1.group.1.name'), $md_1->name);
        $this->assertEquals(data_get($data, 'features.1.group.1.value'), $val_4);
        $this->assertEquals(data_get($data, 'features.1.group.1.choiceId'), null);
        // images
        $this->assertEquals(data_get($data, 'images.working_hours_at_the_beg.0'), env('APP_URL') . "/storage/some_path/img_1.png");
        $this->assertEquals(data_get($data, 'images.working_hours_at_the_beg.1'), env('APP_URL') . "/storage/some_path/img_2.png");
        $this->assertEquals(data_get($data, 'images.working_hours_at_the_end.2'), env('APP_URL') . "/storage/some_path/img_3.png");
        $this->assertEquals(data_get($data, 'images.equipment_on_the_field.3'), env('APP_URL') . "/storage/some_path/img_4.png");
        $this->assertEquals(data_get($data, 'images.equipment_on_the_field.4'), env('APP_URL') . "/storage/some_path/img_5.png");
        $this->assertEquals(data_get($data, 'images.me_and_equipment.5'), env('APP_URL') . "/storage/some_path/img_6.png");
        $this->assertEquals(data_get($data, 'images.others.6'), env('APP_URL') . "/storage/some_path/img_7.png");
        $this->assertEquals(data_get($data, 'images.signature'), env('APP_URL') . "/storage/some_path/img_8.png");
        // video
        $this->assertEquals(data_get($data, 'video'), env('APP_URL') . "/api/report/download-video/{$report->id}");
        // translates
        $this->assertEquals(data_get($data, 'translates.product_specialist'), 'product_specialist');
        $this->assertEquals(data_get($data, 'translates.account_name'), 'account_name');
        $this->assertEquals(data_get($data, 'translates.country'), 'country');
        $this->assertEquals(data_get($data, 'translates.email'), 'email');
        $this->assertEquals(data_get($data, 'translates.phone'), 'phone');
        $this->assertEquals(data_get($data, 'translates.dealer'), 'dealer');
        $this->assertEquals(data_get($data, 'translates.dealer_id'), 'dealer_id');
        $this->assertEquals(data_get($data, 'translates.salesman_name'), 'salesman_name');
        $this->assertEquals(data_get($data, 'translates.customer'), 'customer');
        $this->assertEquals(data_get($data, 'translates.company_name'), 'company_name');
        $this->assertEquals(data_get($data, 'translates.last_name'), 'last_name');
        $this->assertEquals(data_get($data, 'translates.first_name'), 'first_name');
        $this->assertEquals(data_get($data, 'translates.product_name'), 'product_name');
        $this->assertEquals(data_get($data, 'translates.customer_type'), 'customer_type');
        $this->assertEquals(data_get($data, 'translates.potencial'), 'potencial');
        $this->assertEquals(data_get($data, 'translates.competitor'), 'competitor');
        $this->assertEquals(data_get($data, 'translates.product'), 'product');
        $this->assertEquals(data_get($data, 'translates.equipment_group'), 'equipment_group');
        $this->assertEquals(data_get($data, 'translates.model_description'), 'model_description');
        $this->assertEquals(data_get($data, 'translates.machine_serial_number'), 'machine_serial_number');
        $this->assertEquals(data_get($data, 'translates.manufacturer'), 'manufacturer');
        $this->assertEquals(data_get($data, 'translates.header_brand'), 'header_brand');
        $this->assertEquals(data_get($data, 'translates.header_model'), 'header_model');
        $this->assertEquals(data_get($data, 'translates.serial_number_header'), 'serial_number_header');
        $this->assertEquals(data_get($data, 'translates.images'), 'images');
        $this->assertEquals(data_get($data, 'translates.working_hours_at_the_beg'), 'whatb');
        $this->assertEquals(data_get($data, 'translates.working_hours_at_the_end'), 'whate');
        $this->assertEquals(data_get($data, 'translates.equipment_on_the_field'), 'eotf');
        $this->assertEquals(data_get($data, 'translates.me_and_equipment'), 'mam');
        $this->assertEquals(data_get($data, 'translates.others'), 'images_others');
        $this->assertEquals(data_get($data, 'translates.video'), 'video');
        $this->assertEquals(data_get($data, 'translates.demo_assigment'), 'demo_assigment');
        $this->assertEquals(data_get($data, 'translates.demo_resultes'), 'demo_resultes');
        $this->assertEquals(data_get($data, 'translates.client_comment'), 'client_comment');
        $this->assertEquals(data_get($data, 'translates.signature'), 'signature');
        $this->assertEquals(data_get($data, 'translates.location'), 'location');
        $this->assertEquals(data_get($data, 'translates.values'), 'values');
        $this->assertEquals(data_get($data, 'translates.units'), 'units');
        $this->assertEquals(data_get($data, 'translates.download_link'), 'download_link');
        $this->assertEquals(data_get($data, 'translates.quantity_machine'), 'quantity_machine');
        $this->assertEquals(data_get($data, 'translates.trailed_equipment_type'), 'trailed_equipment_type');
        $this->assertEquals(data_get($data, 'translates.independent_equipment'), 'independent_equipment');
        $this->assertEquals(data_get($data, 'translates.machine_with_trailer'), 'machine_with_trailer');
        $this->assertEquals(data_get($data, 'translates.for machine'), 'for machine');
        $this->assertEquals(data_get($data, 'translates.trailer_model'), 'trailer_model');
        $this->assertEquals(data_get($data, 'translates.main_machines'), 'main_machines');
        $this->assertEquals(data_get($data, 'translates.field_condition'), 'field_condition');
        $this->assertEquals($data['translates']['model_description.type'], 'model_description.type');
        $this->assertEquals($data['translates']['model_description.size'], 'model_description.size');
        $this->assertEquals($data['translates']['model_description.size_parameters'], 'model_description.size_parameters');
    }

    private function imagesSave($id)
    {
        $image_1 = new Image();
        $image_1->model = Image::WORKING_START;
        $image_1->entity_type = Report::class;
        $image_1->entity_id = $id;
        $image_1->url = "some_path/img_1.png";
        $image_1->basename = "img_1.png";
        $image_1->save();

        $image_2 = new Image();
        $image_2->model = Image::WORKING_START;
        $image_2->entity_type = Report::class;
        $image_2->entity_id = $id;
        $image_2->url = "some_path/img_2.png";
        $image_2->basename = "img_2.png";
        $image_2->save();

        $image_3 = new Image();
        $image_3->model = Image::WORKING_END;
        $image_3->entity_type = Report::class;
        $image_3->entity_id = $id;
        $image_3->url = "some_path/img_3.png";
        $image_3->basename = "img_3.png";
        $image_3->save();

        $image_4 = new Image();
        $image_4->model = Image::EQUIPMENT;
        $image_4->entity_type = Report::class;
        $image_4->entity_id = $id;
        $image_4->url = "some_path/img_4.png";
        $image_4->basename = "img_4.png";
        $image_4->save();

        $image_5 = new Image();
        $image_5->model = Image::EQUIPMENT;
        $image_5->entity_type = Report::class;
        $image_5->entity_id = $id;
        $image_5->url = "some_path/img_5.png";
        $image_5->basename = "img_5.png";
        $image_5->save();

        $image_6 = new Image();
        $image_6->model = Image::ME;
        $image_6->entity_type = Report::class;
        $image_6->entity_id = $id;
        $image_6->url = "some_path/img_6.png";
        $image_6->basename = "img_6.png";
        $image_6->save();

        $image_7 = new Image();
        $image_7->model = Image::OTHERS;
        $image_7->entity_type = Report::class;
        $image_7->entity_id = $id;
        $image_7->url = "some_path/img_7.png";
        $image_7->basename = "img_7.png";
        $image_7->save();

        $image_8 = new Image();
        $image_8->model = Image::SIGNATURE;
        $image_8->entity_type = Report::class;
        $image_8->entity_id = $id;
        $image_8->url = "some_path/img_8.png";
        $image_8->basename = "img_8.png";
        $image_8->save();
    }

    /** @test */
    public function success_with_translates(): void
    {
        list($en, $ru) = ['en', 'ru'];
        $user = $this->userBuilder->setLang($en)->create();

        $report = $this->reportBuilder->setUser($user)->create();

        $tran_1 = $this->translationBuilder->setAlias('product_specialist')->setLang($en)->create();
        $tran_2 = $this->translationBuilder->setAlias('account_name')->setLang($en)->create();
        $tran_3 = $this->translationBuilder->setAlias('country')->setLang($en)->create();
        $tran_4 = $this->translationBuilder->setAlias('email')->setLang($en)->create();
        $tran_5 = $this->translationBuilder->setAlias('phone')->setLang($en)->create();
        $tran_6 = $this->translationBuilder->setAlias('dealer')->setLang($en)->create();
        $tran_7 = $this->translationBuilder->setAlias('dealer_id')->setLang($en)->create();
        $tran_9 = $this->translationBuilder->setAlias('salesman_name')->setLang($en)->create();
        $tran_10 = $this->translationBuilder->setAlias('customer')->setLang($en)->create();
        $tran_11 = $this->translationBuilder->setAlias('company_name')->setLang($en)->create();
        $tran_12 = $this->translationBuilder->setAlias('last_name')->setLang($en)->create();
        $tran_13 = $this->translationBuilder->setAlias('first_name')->setLang($en)->create();
        $tran_14 = $this->translationBuilder->setAlias('product_name')->setLang($en)->create();
        $tran_15 = $this->translationBuilder->setAlias('customer_type')->setLang($en)->create();
        $tran_16 = $this->translationBuilder->setAlias('potencial')->setLang($en)->create();
        $tran_17 = $this->translationBuilder->setAlias('competitor')->setLang($en)->create();
        $tran_18 = $this->translationBuilder->setAlias('product')->setLang($en)->create();
        $tran_19 = $this->translationBuilder->setAlias('equipment_group')->setLang($en)->create();
        $tran_20 = $this->translationBuilder->setAlias('model_description')->setLang($en)->create();
        $tran_21 = $this->translationBuilder->setAlias('machine_serial_number')->setLang($en)->create();
        $tran_22 = $this->translationBuilder->setAlias('manufacturer')->setLang($en)->create();
        $tran_23 = $this->translationBuilder->setAlias('header_brand')->setLang($en)->create();
        $tran_24 = $this->translationBuilder->setAlias('header_model')->setLang($en)->create();
        $tran_25 = $this->translationBuilder->setAlias('serial_number_header')->setLang($en)->create();
        $tran_26 = $this->translationBuilder->setAlias('images')->setLang($en)->create();
        $tran_27 = $this->translationBuilder->setAlias('whatb')->setLang($en)->create();
        $tran_28 = $this->translationBuilder->setAlias('whate')->setLang($en)->create();
        $tran_29 = $this->translationBuilder->setAlias('eotf')->setLang($en)->create();
        $tran_30 = $this->translationBuilder->setAlias('mam')->setLang($en)->create();
        $tran_31 = $this->translationBuilder->setAlias('images_others')->setLang($en)->create();
        $tran_32 = $this->translationBuilder->setAlias('video')->setLang($en)->create();
        $tran_33 = $this->translationBuilder->setAlias('demo_assigment')->setLang($en)->create();
        $tran_34 = $this->translationBuilder->setAlias('demo_resultes')->setLang($en)->create();
        $tran_35 = $this->translationBuilder->setAlias('client_comment')->setLang($en)->create();
        $tran_36 = $this->translationBuilder->setAlias('signature')->setLang($en)->create();
        $tran_37 = $this->translationBuilder->setAlias('location')->setLang($en)->create();
        $tran_38 = $this->translationBuilder->setAlias('values')->setLang($en)->create();
        $tran_39 = $this->translationBuilder->setAlias('units')->setLang($en)->create();
        $tran_40 = $this->translationBuilder->setAlias('download_link')->setLang($en)->create();
        $tran_41 = $this->translationBuilder->setAlias('quantity')->setLang($en)->create();
        $tran_42 = $this->translationBuilder->setAlias('trailed_equipment_type')->setLang($en)->create();
        $tran_43 = $this->translationBuilder->setAlias('independent_equipment')->setLang($en)->create();
        $tran_44 = $this->translationBuilder->setAlias('machine_with_trailer')->setLang($en)->create();
        $tran_45 = $this->translationBuilder->setAlias('for machine')->setLang($en)->create();
        $tran_46 = $this->translationBuilder->setAlias('trailer_model')->setLang($en)->create();
        $tran_47 = $this->translationBuilder->setAlias('main_machines')->setLang($en)->create();
        $tran_48 = $this->translationBuilder->setAlias('field_condition')->setLang($en)->create();
        $tran_49 = $this->translationBuilder->setAlias('model_description.type')->setLang($en)->create();
        $tran_50 = $this->translationBuilder->setAlias('model_description.size')->setLang($en)->create();
        $tran_51 = $this->translationBuilder->setAlias('model_description.size_parameters')->setLang($en)->create();

        $data =  resolve(CustomReportPdfResource::class)->fill($report);

        // report
        $this->assertNull(data_get($data, 'title'));
        $this->assertEquals(data_get($data, 'salesman_name'), $report->salesman_name);
        $this->assertEquals(data_get($data, 'assignment'), $report->assignment);
        $this->assertEquals(data_get($data, 'client_comment'), $report->client_comment);
        $this->assertEquals(data_get($data, 'demo_result'), $report->result);
        // user
        $this->assertEquals(data_get($data, 'user_full_name'), " ");
        $this->assertNull(data_get($data, 'user_country'));
        $this->assertEquals(data_get($data, 'user_email'), $user->email);
        $this->assertEquals(data_get($data, 'user_phone'), $user->phone);
        // dealer
        $this->assertNull(data_get($data, 'dealer_name'));
        $this->assertNull(data_get($data, 'dealer_country'));
        $this->assertNull(data_get($data, 'dealer_id'));
        // disclaimer
        $this->assertNull(data_get($data, 'disclaimer'));
        // location
        $this->assertEquals(data_get($data, 'location'), "");
        $this->assertNull(data_get($data, 'location_lat'));
        $this->assertNull(data_get($data, 'location_long'));
        // clients
        $this->assertNull(data_get($data, 'customers'));
        // machine
        $this->assertNull(data_get($data, 'machines'));
        // features
        $this->assertNull(data_get($data, 'features'));
        // images
        $this->assertNull(data_get($data, 'images'));
        // video
        $this->assertNull(data_get($data, 'video'));
        // translates
        $this->assertEquals(data_get($data, 'translates.product_specialist'), $tran_1->text);
        $this->assertEquals(data_get($data, 'translates.account_name'), $tran_2->text);
        $this->assertEquals(data_get($data, 'translates.country'), $tran_3->text);
        $this->assertEquals(data_get($data, 'translates.email'), $tran_4->text);
        $this->assertEquals(data_get($data, 'translates.phone'), $tran_5->text);
        $this->assertEquals(data_get($data, 'translates.dealer'), $tran_6->text);
        $this->assertEquals(data_get($data, 'translates.dealer_id'), $tran_7->text);
        $this->assertEquals(data_get($data, 'translates.salesman_name'), $tran_9->text);
        $this->assertEquals(data_get($data, 'translates.customer'), $tran_10->text);
        $this->assertEquals(data_get($data, 'translates.company_name'), $tran_11->text);
        $this->assertEquals(data_get($data, 'translates.last_name'), $tran_12->text);
        $this->assertEquals(data_get($data, 'translates.first_name'), $tran_13->text);
        $this->assertEquals(data_get($data, 'translates.product_name'), $tran_14->text);
        $this->assertEquals(data_get($data, 'translates.customer_type'), $tran_15->text);
        $this->assertEquals(data_get($data, 'translates.potencial'), $tran_16->text);
        $this->assertEquals(data_get($data, 'translates.competitor'), $tran_17->text);
        $this->assertEquals(data_get($data, 'translates.product'), $tran_18->text);
        $this->assertEquals(data_get($data, 'translates.equipment_group'), $tran_19->text);
        $this->assertEquals(data_get($data, 'translates.model_description'), $tran_20->text);
        $this->assertEquals(data_get($data, 'translates.machine_serial_number'), $tran_21->text);
        $this->assertEquals(data_get($data, 'translates.manufacturer'), $tran_22->text);
        $this->assertEquals(data_get($data, 'translates.header_brand'), $tran_23->text);
        $this->assertEquals(data_get($data, 'translates.header_model'), $tran_24->text);
        $this->assertEquals(data_get($data, 'translates.serial_number_header'), $tran_25->text);
        $this->assertEquals(data_get($data, 'translates.images'), $tran_26->text);
        $this->assertEquals(data_get($data, 'translates.working_hours_at_the_beg'), $tran_27->text);
        $this->assertEquals(data_get($data, 'translates.working_hours_at_the_end'), $tran_28->text);
        $this->assertEquals(data_get($data, 'translates.equipment_on_the_field'), $tran_29->text);
        $this->assertEquals(data_get($data, 'translates.me_and_equipment'), $tran_30->text);
        $this->assertEquals(data_get($data, 'translates.others'), $tran_31->text);
        $this->assertEquals(data_get($data, 'translates.video'), $tran_32->text);
        $this->assertEquals(data_get($data, 'translates.demo_assigment'), $tran_33->text);
        $this->assertEquals(data_get($data, 'translates.demo_resultes'), $tran_34->text);
        $this->assertEquals(data_get($data, 'translates.client_comment'), $tran_35->text);
        $this->assertEquals(data_get($data, 'translates.signature'), $tran_36->text);
        $this->assertEquals(data_get($data, 'translates.location'), $tran_37->text);
        $this->assertEquals(data_get($data, 'translates.values'), $tran_38->text);
        $this->assertEquals(data_get($data, 'translates.units'), $tran_39->text);
        $this->assertEquals(data_get($data, 'translates.download_link'), $tran_40->text);
        $this->assertEquals(data_get($data, 'translates.quantity_machine'), $tran_41->text);
        $this->assertEquals(data_get($data, 'translates.trailed_equipment_type'), $tran_42->text);
        $this->assertEquals(data_get($data, 'translates.independent_equipment'), $tran_43->text);
        $this->assertEquals(data_get($data, 'translates.machine_with_trailer'), $tran_44->text);
        $this->assertEquals(data_get($data, 'translates.for machine'), $tran_45->text);
        $this->assertEquals(data_get($data, 'translates.trailer_model'), $tran_46->text);
        $this->assertEquals(data_get($data, 'translates.main_machines'), $tran_47->text);
        $this->assertEquals(data_get($data, 'translates.field_condition'), $tran_48->text);
        $this->assertEquals($data['translates']['model_description.type'], $tran_49->text);
        $this->assertEquals($data['translates']['model_description.size'], $tran_50->text);
        $this->assertEquals($data['translates']['model_description.size_parameters'], $tran_51->text);
    }
}
