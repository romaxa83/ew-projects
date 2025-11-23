<?php

namespace Tests\Feature\Ringostat\Webhook;

use App\Models\Calls\IncomingCall;
use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\IncomingCallBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Ringostat;
use Tests\TestCase;

class HandlerTakingCallTest extends TestCase
{
    use DatabaseTransactions;

    protected IncomingCallBuilder $incomingCallBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected Ringostat\EventBeforeCallBuilder $callBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->incomingCallBuilder = resolve(IncomingCallBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->callBuilder = resolve(Ringostat\EventBeforeCallBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();

        $this->data = [
            "duration_conversation" => null,
            "duration_call" => null,
            "duration_call_ms" => null,
            "duration_waiting" => null,
            "call_card_link" => null,
            "type" => "in",
            "call_date" => "2024-10-08 17:47:25",
            "call_timestamp" => "1728427645501605",
            "connected_with" => "h2hmoverscom_jena",
            "status" => null,
            "destination" => "17732368797",
            "number_e164" => "+13129191965",
            "employee" => null,
            "employee_estension" => null,
            "recording_presence" => null,
            "recording" => null,
            "recording_wav" => null,
            "employee_id" => 331851,
            "unique_call" => "0",
            "call_id" => "us1_-1728427645.1098915",
            "caller_number" => "13129191965",
            "project_id" => "110070",
        ];
    }

    /** @test */
    public function handler_taking_call()
    {
        $data = $this->data;

        $event = $this->callBuilder
            ->call_id($data['call_id'])
            ->create();

        $incomingCall = $this->incomingCallBuilder
            ->call_id($data['call_id'])
            ->create();
        $incomingCallId = $incomingCall->id;

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->ringostat_id($data['employee_id'])
            ->create();

        $this->assertNull($employee->ringostat_call_rec_id);

        $this->post(route('ringostat.handleTakingCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $employee->refresh();

        $this->assertEquals($employee->ringostat_call_rec_id, $event->id);
        $this->assertEquals($employee->callers_number, $event->callers_number);

        $this->assertNull(IncomingCall::find($incomingCallId));
    }

    /** @test */
    public function handler_taking_call_if_location_forwarding()
    {
        $data = $this->data;

        $event = $this->callBuilder
            ->call_id($data['call_id'])
            ->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->ringostat_id($data['employee_id'])
            ->create();

        /** @var $employee_1 Employee */
        $employee_1 = $this->employeeBuilder
            ->ringostat_id($data['employee_id'])
            ->ringostat_call_rec_id('3214324234')
            ->callers_number($event->callers_number)
            ->create();

        $this->assertNull($employee->ringostat_call_rec_id);
        $this->assertNotNull($employee_1->ringostat_call_rec_id);

        $this->post(route('ringostat.handleTakingCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $employee->refresh();
        $employee_1->refresh();

        $this->assertEquals($employee->ringostat_call_rec_id, $event->id);
        $this->assertEquals($employee->callers_number, $event->callers_number);

        $this->assertNull($employee_1->ringostat_call_rec_id);
        $this->assertNull($employee_1->callers_number);
    }

    /** @test */
    public function handler_taking_call_not_found_employee()
    {
        $data = $this->data;

        $event = $this->callBuilder
            ->call_id($data['call_id'])
            ->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->ringostat_id($data['employee_id'] . 1)
            ->create();

        $this->assertNull($employee->ringostat_call_rec_id);

        $this->post(route('ringostat.handleTakingCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $employee->refresh();

        $this->assertNull($employee->ringostat_call_rec_id);
    }

    /** @test */
    public function handler_taking_call_not_found_event()
    {
        $data = $this->data;

        $event = $this->callBuilder
            ->call_id($data['call_id'].'1')
            ->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->ringostat_id($data['employee_id'])
            ->create();

        $this->assertNull($employee->ringostat_call_rec_id);

        $this->post(route('ringostat.handleTakingCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $employee->refresh();

        $this->assertNull($employee->ringostat_call_rec_id);
    }
}
