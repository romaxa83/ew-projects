<?php

namespace Tests\Feature\Api\Report\Update\Ps;

use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\Feature\Api\Report\Create\CreateTest;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdateTest extends TestCase
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

        $data = CreateTest::fullData();

        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->assertNotEquals($rep->salesman_name, data_get($data, 'salesman_name'));
        $this->assertNotEquals($rep->assignment, data_get($data, 'assignment'));
        $this->assertNotEquals($rep->result, data_get($data, 'result'));
        $this->assertNotEquals($rep->client_comment, data_get($data, 'client_comment'));
        $this->assertNotEquals($rep->client_email, data_get($data, 'client_email'));
        $this->assertNull($rep->location);
        $this->assertNull($rep->planned_at);
        $this->assertNull($rep->pushData);

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "status" => ReportStatus::IN_PROCESS,
                    "user" => ["id" => $user->id],
                    "machine" => [],
                    "clients" => [
                        "john_dear_client" => [],
                        "report_client" => [],
                    ],
                    "location" => [
                        "lat" => data_get($data, 'location.location_lat'),
                        "long" => data_get($data, 'location.location_long'),
                        "country" => data_get($data, 'location.location_country'),
                        "city" => data_get($data, 'location.location_city'),
                        "region" => data_get($data, 'location.location_region'),
                        "zipcode" => data_get($data, 'location.location_zipcode'),
                        "street" => data_get($data, 'location.location_street'),
                    ],
                    "images" => [],
                    "salesman_name" => data_get($data, 'salesman_name'),
                    "assignment" => data_get($data, 'assignment'),
                    "result" => data_get($data, 'result'),
                    "client_comment" => data_get($data, 'client_comment'),
                    "client_email" => data_get($data, 'client_email'),
                    "planned_at" => null
                ],
                "success" => true
            ])
        ;

        $rep->refresh();

        $this->assertNull($rep->planned_at);
        $this->assertNull($rep->pushData);
    }

    /** @test */
    public function success_not_update_location_if_exist_it()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::fullData();

        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setCountry("UK")
            ->setUser($user)->create();

        $this->assertNotEquals($rep->location->lat, data_get($data, 'location.location_lat'));
        $this->assertNotEquals($rep->location->long, data_get($data, 'location.location_long'));
        $this->assertNotEquals($rep->location->country, data_get($data, 'location.location_country'));
        $this->assertNotEquals($rep->location->city, data_get($data, 'location.location_city'));
        $this->assertNotEquals($rep->location->region, data_get($data, 'location.location_region'));
        $this->assertNotEquals($rep->location->zipcode, data_get($data, 'location.location_zipcode'));
        $this->assertNotEquals($rep->location->street, data_get($data, 'location.location_street'));
        $this->assertNotEquals($rep->location->district, data_get($data, 'location.location_district'));

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => ["id" => $rep->id,]
            ])
        ;

        $rep->refresh();

        $this->assertNotEquals($rep->location->lat, data_get($data, 'location.location_lat'));
        $this->assertNotEquals($rep->location->long, data_get($data, 'location.location_long'));
        $this->assertNotEquals($rep->location->country, data_get($data, 'location.location_country'));
        $this->assertNotEquals($rep->location->city, data_get($data, 'location.location_city'));
        $this->assertNotEquals($rep->location->region, data_get($data, 'location.location_region'));
        $this->assertNotEquals($rep->location->zipcode, data_get($data, 'location.location_zipcode'));
        $this->assertNotEquals($rep->location->street, data_get($data, 'location.location_street'));
        $this->assertNotEquals($rep->location->district, data_get($data, 'location.location_district'));
    }

    /** @test */
    public function success_if_empty_data()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setCountry("UK")
            ->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), [])
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "status" => ReportStatus::IN_PROCESS,
                    "user" => ["id" => $user->id],
                    "machine" => [],
                    "clients" => [
                        "john_dear_client" => [],
                        "report_client" => [],
                    ],
                    "location" => [
                        "lat" => $rep->location->lat,
                        "long" => $rep->location->long,
                        "country" => $rep->location->country,
                        "city" => $rep->location->city,
                        "region" => $rep->location->region,
                        "zipcode" => $rep->location->zipcode,
                        "street" => $rep->location->street,
                    ],
                    "images" => [],
                    "salesman_name" => $rep->salesman_name,
                    "assignment" => $rep->assignment,
                    "result" => $rep->result,
                    "client_comment" => $rep->client_comment,
                    "client_email" => $rep->client_email,
                ]
            ])
        ;
    }

    /** @test */
    public function success_open_status()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep_1]), [])
            ->assertJson([
                "data" => ["id" => $rep_1->id]
            ])
        ;
        $this->postJson(route('api.report.update.ps', ['report' => $rep_2]), [])
            ->assertJson([
                "data" => ["id" => $rep_2->id]
            ])
        ;
    }

    /** @test */
    public function fail_not_open_status()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::EDITED)->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setStatus(ReportStatus::VERIFY)->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep_1]), [])
            ->assertJson($this->structureErrorResponse(__('message.report_not_open_for_edit')))
        ;
        $this->postJson(route('api.report.update.ps', ['report' => $rep_2]), [])
            ->assertJson($this->structureErrorResponse(__('message.report_not_open_for_edit')))
        ;
        $this->postJson(route('api.report.update.ps', ['report' => $rep_3]), [])
            ->assertJson($this->structureErrorResponse(__('message.report_not_open_for_edit')))
        ;
    }

    /** @test */
    public function fail_not_owner()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $user_1 = $this->userBuilder->setRole($role)->create();

        $rep = $this->reportBuilder->setUser($user_1)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), [])
            ->assertJson($this->structureErrorResponse(__('message.report_not_edit')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();

        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), [])
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

