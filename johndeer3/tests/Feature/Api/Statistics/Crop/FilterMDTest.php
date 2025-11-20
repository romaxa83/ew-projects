<?php
//
//namespace Tests\Feature\Api\Statistics\Crop;
//
//use App\Models\JD\Dealer;
//use App\Models\JD\ModelDescription;
//use App\Models\Report\Feature\Feature;
//use App\Models\User\Role;
//use App\Type\ReportStatus;
//use Carbon\Carbon;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
//use Illuminate\Http\Response;
//use Tests\Builder\Feature\FeatureBuilder;
//use Tests\Builder\Report\ReportBuilder;
//use Tests\Builder\UserBuilder;
//use Tests\TestCase;
//use Tests\Traits\ResponseStructure;
//
//class FilterMDTest extends TestCase
//{
//    use DatabaseTransactions;
//    use ResponseStructure;
//
//    protected $userBuilder;
//    protected $reportBuilder;
//    protected $featureBuilder;
//
//    public function setUp(): void
//    {
//        parent::setUp();
//        $this->passportInit();
//        $this->userBuilder = resolve(UserBuilder::class);
//        $this->reportBuilder = resolve(ReportBuilder::class);
//        $this->featureBuilder = resolve(FeatureBuilder::class);
//    }
//
//    /** @test */
//    public function success()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
//        $this->assertNotNull($dealerFR_2);
//
//        $de = "DE - German";
//        $dealerDE = Dealer::query()->where('country', $de)->first();
//        $this->assertNotNull($dealerDE);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()->first();
//
//        $md_1_1 = ModelDescription::query()
//            ->where([
//                ['id', '!=', $md_1->id],
//                ['eg_jd_id', $md_1->eg_jd_id],
//            ])
//            ->first();
//
//        $md_2 = ModelDescription::query()
//            ->where('eg_jd_id', '!=', $md_1->eg_jd_id)
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $val_2_2 = 'val_2_@';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        $feature_2 = $this->featureBuilder
//            ->setValues($val_2_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//                ["id" => $feature_2->id, "group" => [
//                    ["choiceId" => $feature_2->values[0]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $this->reportBuilder->setUser($userFR_1_1)
//            ->setModelDescription($md_1_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        // report not check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[1]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_2)
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $this->assertEquals($feature_1->values[0]->current->name, $val_1);
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureSuccessResponse([
//                $md_1->id => $md_1->name . ' (1)',
//                $md_1_1->id => $md_1_1->name . ' (2)',
//            ]))
//        ;
//    }
//
//    /** @test */
//    public function success_some_fields()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
//        $this->assertNotNull($dealerFR_2);
//
//        $de = "DE - German";
//        $dealerDE = Dealer::query()->where('country', $de)->first();
//        $this->assertNotNull($dealerDE);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        $userDE = $this->userBuilder->setDealer($dealerDE)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $md_1_1 = ModelDescription::query()
//            ->with('product')
//            ->where([
//                ['id', '!=', $md_1->id],
//                ['eg_jd_id', $md_1->eg_jd_id],
//            ])
//            ->first();
//
//        $md_2 = ModelDescription::query()
//            ->with('product')
//            ->where('eg_jd_id', '!=', $md_1->eg_jd_id)
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $val_2_2 = 'val_2_@';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        $feature_2 = $this->featureBuilder
//            ->setValues($val_2_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//                ["id" => $feature_2->id, "group" => [
//                    ["choiceId" => $feature_2->values[0]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $this->reportBuilder->setUser($userFR_1_1)
//            ->setModelDescription($md_1_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[1]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[1]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        // report not check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_2)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[1]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//        $this->reportBuilder->setUser($userDE)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]]
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $this->assertEquals($feature_1->values[0]->current->name, $val_1);
//        $this->assertEquals($feature_1->values[1]->current->name, $val_2);
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id.','.$feature_1->values[1]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureSuccessResponse([
//                $md_1->id => $md_1->name . ' (3)',
//                $md_1_1->id => $md_1_1->name . ' (2)',
//            ]))
//        ;
//    }
//
//    /** @test */
//    public function success_last_year()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureSuccessResponse([]))
//        ;
//    }
//
//    /** @test */
//    public function fail_not_crop_feature()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureErrorResponse('Not found a crop data'))
//        ;
//    }
//
//    /** @test */
//    public function fail_without_year()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureErrorResponse(['The year field is required.']))
//        ;
//    }
//
//    /** @test */
//    public function fail_without_status()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureErrorResponse(['The status field is required.']))
//        ;
//    }
//
//    /** @test */
//    public function fail_without_country()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureErrorResponse(['The country field is required.']))
//        ;
//    }
//
//    /** @test */
//    public function fail_without_dealer()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureErrorResponse(['The dealer field is required.']))
//        ;
//    }
//
//    /** @test */
//    public function fail_without_eg()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureErrorResponse(['The eg field is required.']))
//        ;
//    }
//
//    /** @test */
//    public function fail_without_crop()
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureErrorResponse(['The crop field is required.']))
//        ;
//    }
//
//    /** @test */
//    public function not_admin()
//    {
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//        $this->loginAsUser($userFR_1);
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertStatus(Response::HTTP_FORBIDDEN)
//            ->assertJson($this->structureErrorResponse(__('message.no_access')))
//        ;
//    }
//
//    /** @test */
//    public function not_auth()
//    {
//        $admin = $this->userBuilder->create();
//
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $fr = "FR - French";
//        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
//        $this->assertNotNull($dealerFR_1);
//
//        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
//            ->setRole($role)->create();
//
//        // MODEL DESCRIPTIONS
//        $md_1 = ModelDescription::query()
//            ->with('product')
//            ->first();
//
//        $val_1 = 'val_1';
//        $val_2 = 'val_2';
//        $feature_1 = $this->featureBuilder
//            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
//            ->setValues($val_1, $val_2)
//            ->withTranslation()
//            ->create();
//
//        // report check
//        $this->reportBuilder->setUser($userFR_1)
//            ->setModelDescription($md_1)
//            ->setFeatures([
//                ["id" => $feature_1->id, "group" => [
//                    ["choiceId" => $feature_1->values[0]->id]
//                ]],
//            ])
//            ->setStatus(ReportStatus::CREATED)->create();
//
//        $data = [
//            'year' => Carbon::now()->year,
//            'status' => ReportStatus::CREATED,
//            'country' => $fr,
//            'dealer' => $dealerFR_1->id,
//            'eg' => $md_1->eg_jd_id,
//            'crop' => $feature_1->values[0]->id
//        ];
//
//        $this->getJson(route('api.statistic.crop.filter.md', $data),[
//            'Content-Language' => \App::getLocale()
//        ])
//            ->assertJson($this->structureErrorResponse("Unauthenticated."))
//        ;
//    }
//}
