<?php

namespace Tests\Feature\Ringostat\Webhook;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Ringostat\EventBeforeCall;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Ringostat;
use Tests\TestCase;

class HandlerBeforeOutCallTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $clientPhoneBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected Ringostat\EventBeforeCallBuilder $callBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->clientPhoneBuilder = resolve(PhoneBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->callBuilder = resolve(Ringostat\EventBeforeCallBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();

        $this->data = [
            "call_type" => "out",
            "call_date_microsecond" => "1728372216699252",
            "destination" => "+380954514991",
            "number_e164" => "+380954514992",
            "callers_number" => "+380954514994",
        ];
    }

    /** @test */
    public function create()
    {
        $ringostatProjectId = '1111';
        /** @var $division Division */
        $this->divisionBuilder
            ->misc(['ringostat_project_id' => $ringostatProjectId])
            ->create();

        $data = $this->data;
        $data['project_id'] = $ringostatProjectId;

        $this->assertEquals(0, EventBeforeCall::count());
        $this->assertEquals(0, CommunicationRecord::count());

        $this->post(route('ringostat.handleBeforeOutCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $this->assertEquals(0, CommunicationRecord::count());

        /** @var $model EventBeforeCall */
        $model = EventBeforeCall::first();

        $this->assertEquals($model->project_id, $data['project_id']);
        $this->assertEquals($model->call_type, $data['call_type']);
        $this->assertEquals($model->call_date_microsecond, $data['call_date_microsecond']);
        $this->assertEquals($model->destination, $data['destination']);
        $this->assertEquals($model->number_e164, $data['number_e164']);
        $this->assertEquals($model->callers_number, $data['callers_number']);
        $this->assertNull($model->employee_ringostat_id);
        $this->assertNull($model->client_id);
        $this->assertNull($model->call_date);
        $this->assertNull($model->call_id);
        $this->assertNull($model->extension_number);
        $this->assertNull($model->responsible_employees);
    }

    /** @test */
    public function create_attach_employee_by_sip_direction()
    {
        $this->employeeBuilder->create();
        $miscs_1 = [
            'sip_direction' => 'h2hmoverscom_rebekah',
            'ext_number' => '104',
        ];
        /** @var $employee_1 Employee */
        $employee_1 = $this->employeeBuilder
            ->ringostat_id(22)
            ->ringostat_miscs($miscs_1)
            ->create();

        $miscs_2 = [
            'sip_direction' => 'h2hmoverscom_rebekah_2',
            'ext_number' => '105',
        ];
        $employee_2 = $this->employeeBuilder
            ->ringostat_miscs($miscs_2)->create();
        $this->employeeBuilder->create();

        $data = $this->data;
        $data['project_id'] = 1111;
        $data['callers_number'] = $miscs_1['sip_direction'];

        $this->assertNull($employee_1->ringostat_call_rec_id);
        $this->assertNull($employee_2->ringostat_call_rec_id);

        $this->post(route('ringostat.handleBeforeOutCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $employee_1->refresh();
        $employee_2->refresh();

        /** @var $model EventBeforeCall */
        $model = EventBeforeCall::first();

        $this->assertEquals($model->id, $employee_1->ringostat_call_rec_id);
        $this->assertNull($model->client_id);
        $this->assertNull($employee_2->ringostat_call_rec_id);
    }

    /** @test */
    public function not_attach_employee_by_sip_direction_if_type_in()
    {
        $miscs_1 = [
            'sip_direction' => 'h2hmoverscom_rebekah',
            'ext_number' => '104',
        ];
        /** @var $employee_1 Employee */
        $employee_1 = $this->employeeBuilder
            ->ringostat_id(22)
            ->ringostat_miscs($miscs_1)
            ->create();

        $data = $this->data;
        $data['project_id'] = 1111;
        $data['callers_number'] = $miscs_1['sip_direction'];
        $data['call_type'] = 'in';

        $this->assertNull($employee_1->ringostat_call_rec_id);

        $this->post(route('ringostat.handleBeforeOutCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $employee_1->refresh();

        /** @var $model EventBeforeCall */
        $model = EventBeforeCall::first();

        $this->assertNull($employee_1->ringostat_call_rec_id);
        $this->assertNull($model->client_id);
    }

    /** @test */
    public function create_attach_client_by_destination_if_type_out()
    {
        $number = '232323232';

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $this->clientPhoneBuilder->value($number)
            ->client($client)->create();
        $this->clientPhoneBuilder->client($client)->create();

        $data = $this->data;
        $data['project_id'] = 1111;
        $data['destination'] = '+'.$number;

        $this->post(route('ringostat.handleBeforeOutCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        /** @var $model EventBeforeCall */
        $model = EventBeforeCall::first();

        $this->assertEquals($model->client_id, $client->id);
    }

    /** @test */
    public function not_attach_client_by_destination_if_type_in()
    {
        $number = '232323232';

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $this->clientPhoneBuilder->value($number)
            ->client($client)->create();

        $data = $this->data;
        $data['project_id'] = 1111;
        $data['destination'] = '+'.$number;
        $data['call_type'] = 'in';

        $this->post(route('ringostat.handleBeforeOutCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        /** @var $model EventBeforeCall */
        $model = EventBeforeCall::first();

        $this->assertNull($model->client_id);
    }

    /** @test */
    public function more_one()
    {
        $this->callBuilder->create();
        $this->callBuilder->create();

        $this->assertEquals(2, EventBeforeCall::count());

        $this->post(route('ringostat.handleBeforeOutCall'), $this->data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $this->assertEquals(3, EventBeforeCall::count());
    }
}


