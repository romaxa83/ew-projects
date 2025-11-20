<?php

namespace Tests\Feature\Api\Report\Admin;

use App\Models\User\Role;
use App\Repositories\Report\LocationRepository;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Builder\UserBuilder;
use App\Models\Report\Location;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Report\ReportBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ListLocationDataForFilterTest extends TestCase
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
    public function success_country_type()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["country" => $uk])->create();
        $this->reportBuilder->setLocationData(["country" => $uk])->create();
        $this->reportBuilder->setLocationData(["country" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $poland => $poland,
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_country_type_empty()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER
        ]))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_country_type_search()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $ukraine = "Ukraine";
        $uganda = "Uganda";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["country" => $uk])->create();
        $this->reportBuilder->setLocationData(["country" => $ukraine])->create();
        $this->reportBuilder->setLocationData(["country" => $uganda])->create();
        $this->reportBuilder->setLocationData(["country" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER,
            "query" => $uk,
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $ukraine => $ukraine,
            ]))
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER,
            "query" => 'u',
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $ukraine => $ukraine,
                $uganda => $uganda,
            ]))
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER,
            "query" => 'po',
        ]))
            ->assertJson($this->structureSuccessResponse([
                $poland => $poland
            ]))
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER,
            "query" => 'au',
        ]))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_country_type_for_statistic()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["country" => $uk])->create();
        $this->reportBuilder->setLocationData(["country" => $uk])->create();
        $this->reportBuilder->setLocationData(["country" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER,
            "forStatistic" => true
        ]))
            ->assertJson($this->structureSuccessResponse([
                $poland => $poland,
                $uk => $uk,
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_region_type()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $poland => $poland,
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_region_type_empty()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->reportBuilder->create();
        $this->reportBuilder->create();
        $this->reportBuilder->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER
        ]))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_region_type_search()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $ukraine = "Ukraine";
        $uganda = "Uganda";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $ukraine])->create();
        $this->reportBuilder->setLocationData(["region" => $uganda])->create();
        $this->reportBuilder->setLocationData(["region" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER,
            "query" => $uk,
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $ukraine => $ukraine,
            ]))
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER,
            "query" => 'u',
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $ukraine => $ukraine,
                $uganda => $uganda,
            ]))
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER,
            "query" => 'po',
        ]))
            ->assertJson($this->structureSuccessResponse([
                $poland => $poland
            ]))
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER,
            "query" => 'au',
        ]))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_region_type_for_statistic()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER,
            "forStatistic" => true
        ]))
            ->assertJson($this->structureSuccessResponse([
                $poland => $poland,
                $uk => $uk,
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_district_type()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["district" => $uk])->create();
        $this->reportBuilder->setLocationData(["district" => $uk])->create();
        $this->reportBuilder->setLocationData(["district" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $poland => $poland,
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_district_type_empty()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $uk])->create();
        $this->reportBuilder->setLocationData(["region" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER
        ]))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_district_type_search()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $ukraine = "Ukraine";
        $uganda = "Uganda";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["district" => $uk])->create();
        $this->reportBuilder->setLocationData(["district" => $ukraine])->create();
        $this->reportBuilder->setLocationData(["district" => $uganda])->create();
        $this->reportBuilder->setLocationData(["district" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
            "query" => $uk,
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $ukraine => $ukraine,
            ]))
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
            "query" => 'u',
        ]))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk,
                $ukraine => $ukraine,
                $uganda => $uganda,
            ]))
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
            "query" => 'po',
        ]))
            ->assertJson($this->structureSuccessResponse([
                $poland => $poland
            ]))
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
            "query" => 'au',
        ]))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_region_type_by_country()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $london = "London";
        $lids = "Lids";
        $visla = "Visla";
        $krakov = "Krakov";

        $this->reportBuilder->setLocationData(["country" => $uk, "region" => $london])->create();
        $this->reportBuilder->setLocationData(["country" => $uk, "region" => $london])->create();
        $this->reportBuilder->setLocationData(["country" => $uk, "region" => $lids])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "region" => $visla])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "region" => $visla])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "region" => $krakov])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER,
            "country" => $uk,
        ]))
            ->assertJson($this->structureSuccessResponse([
                $london => $london,
                $lids => $lids,
            ]))
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_REGION_FILTER,
            "country" => $poland,
        ]))
            ->assertJson($this->structureSuccessResponse([
                $visla => $visla,
                $krakov => $krakov,
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_district_type_by_country()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $london = "London";
        $lids = "Lids";
        $visla = "Visla";
        $krakov = "Krakov";

        $this->reportBuilder->setLocationData(["country" => $uk, "district" => $london])->create();
        $this->reportBuilder->setLocationData(["country" => $uk, "district" => $london])->create();
        $this->reportBuilder->setLocationData(["country" => $uk, "district" => $lids])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "district" => $visla])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "district" => $visla])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "district" => $krakov])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
            "country" => $uk,
        ]))
            ->assertJson($this->structureSuccessResponse([
                $london => $london,
                $lids => $lids,
            ]))
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
            "country" => $poland,
        ]))
            ->assertJson($this->structureSuccessResponse([
                $visla => $visla,
                $krakov => $krakov,
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_country_type_by_country()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $london = "London";
        $lids = "Lids";
        $visla = "Visla";
        $krakov = "Krakov";

        $this->reportBuilder->setLocationData(["country" => $uk, "district" => $london])->create();
        $this->reportBuilder->setLocationData(["country" => $uk, "district" => $london])->create();
        $this->reportBuilder->setLocationData(["country" => $uk, "district" => $lids])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "district" => $visla])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "district" => $visla])->create();
        $this->reportBuilder->setLocationData(["country" => $poland, "district" => $krakov])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER,
            "country" => $uk,
        ]))

            ->assertJson($this->structureSuccessResponse([
                $uk => $uk
            ]))
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_COUNTRY_FILTER,
            "country" => $poland,
        ]))
            ->assertJson($this->structureSuccessResponse([
                $poland => $poland
            ]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function fail_without_type()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["country" => $uk])->create();
        $this->reportBuilder->setLocationData(["country" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "country" => $uk,
        ]))
            ->assertJson($this->structureErrorResponse(["The type field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_type()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $uk = "UK";
        $poland = "Poland";

        $this->reportBuilder->setLocationData(["country" => $uk])->create();
        $this->reportBuilder->setLocationData(["country" => $poland])->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => "wrong",
        ]))
            ->assertJson($this->structureErrorResponse(["The selected type is invalid."]))
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(LocationRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getListByFilter")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
        ]))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
        ]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->userBuilder->create();

        $this->getJson(route('admin.report.list-filter', [
            "type" => Location::TYPE_DISTRICT_FILTER,
        ]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
