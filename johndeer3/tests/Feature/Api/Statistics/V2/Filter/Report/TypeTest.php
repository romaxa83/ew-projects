<?php

namespace Tests\Feature\Api\Statistics\V2\Filter\Report;

use App\Models\JD\Dealer;
use App\Models\JD\ModelDescription;
use App\Services\Statistics\StatisticFilterService;
use App\Type\ModelDescription as MDType;
use App\Models\User\Role;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class TypeTest extends TestCase
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

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
        $this->assertNotNull($dealerFR_2);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_2 = $this->userBuilder->setDealer($dealerFR_2)
            ->setRole($role)->create();

        $userDE = $this->userBuilder->setDealer($dealerDE)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        $md_1_1 = ModelDescription::query()
            ->with('product')
            ->where([
                ['id', '!=', $md_1->id],
                ['eg_jd_id', $md_1->eg_jd_id],
            ])
            ->first();
        $md_1_1->product->update(['type' => MDType::TYPE_ONE]);

        $md_2 = ModelDescription::query()
            ->with('product')
            ->where('eg_jd_id', '!=', $md_1->eg_jd_id)
            ->first();
        $md_2->product->update(['type' => MDType::TYPE_TWO]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1_1)
            ->setModelDescription($md_2)
            ->setStatus(ReportStatus::CREATED)->create();

        // report not check
        $this->reportBuilder->setUser($userDE)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_2)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => [$fr, $de],
            'dealer' => [$dealerFR_1->id, $dealerFR_2->id],
            'eg' => [$md_1->equipmentGroup->id, $md_2->equipmentGroup->id],
            'md' => [$md_1->id, $md_2->id, $md_1_1->id],
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureSuccessResponse([
                MDType::TYPE_ONE => MDType::TYPE_ONE . ' (3)',
                MDType::TYPE_TWO => MDType::TYPE_TWO  . ' (1)',
            ]))
        ;
    }

    /** @test */
    public function success_some_size_null()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
        $this->assertNotNull($dealerFR_2);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        $md_1_1 = ModelDescription::query()
            ->with('product')
            ->where([
                ['id', '!=', $md_1->id],
                ['eg_jd_id', $md_1->eg_jd_id],
            ])
            ->first();
        $md_1_1->product->update(['type' => MDType::TYPE_ONE]);

        $md_2 = ModelDescription::query()
            ->with('product')
            ->where('eg_jd_id', '!=', $md_1->eg_jd_id)
            ->first();
        $md_2->product->update(['type' => null]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1_1)
            ->setStatus(ReportStatus::CREATED)->create();

        // report not check
        $this->reportBuilder->setUser($userFR_1_1)
            ->setModelDescription($md_2)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => [$fr, $de],
            'dealer' => [$dealerFR_1->id, $dealerFR_2->id],
            'eg' => [$md_1->equipmentGroup->id, $md_2->equipmentGroup->id],
            'md' => [$md_1->id, $md_1_1->id],
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureSuccessResponse([
                MDType::TYPE_ONE => MDType::TYPE_ONE . ' (3)',
            ]))
        ;
    }

    /** @test */
    public function success_last_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureSuccessResponse([]))
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureErrorResponse(['The year field is required.']))
        ;
    }

    /** @test */
    public function fail_without_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureErrorResponse(['The status field is required.']))
        ;
    }

    /** @test */
    public function fail_without_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureErrorResponse(['The country field is required.']))
        ;
    }

    /** @test */
    public function fail_without_dealer()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'eg' => $md_1->eg_jd_id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureErrorResponse(['The dealer field is required.']))
        ;
    }

    /** @test */
    public function fail_without_eg()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureErrorResponse(['The eg field is required.']))
        ;
    }

    /** @test */
    public function fail_without_md()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureErrorResponse(['The md field is required.']))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(StatisticFilterService::class, function(MockInterface $mock){
            $mock->shouldReceive("reportTypeData")
                ->andThrows(\Exception::class, "some exception message");
        });

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();


        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $admin = $this->userBuilder->create();

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $this->loginAsUser($userFR_1);

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->userBuilder->create();

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->first();
        $md_1->product->update(['type' => MDType::TYPE_ONE]);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
        ];

        $this->getJson(route('api.v2.statistic.type.filter', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
