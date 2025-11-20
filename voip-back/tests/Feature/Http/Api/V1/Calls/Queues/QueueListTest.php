<?php

namespace Tests\Feature\Http\Api\V1\Calls\Queues;

use App\Models\Calls\Queue;
use App\Models\Employees\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class QueueListTest extends TestCase
{
    use DatabaseTransactions;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected QueueBuilder $queueBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function get_list()
    {
        $this->queueBuilder->create();
        $this->queueBuilder->create();

        $this->get(
            route('api.v1.calls.queues'),
            $this->headers()
        )
            ->assertOk()
            ->assertJsonStructure(['data' => [ 0 => [
                'id',
            ]]])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function get_list_assert_data()
    {
        $sip = $this->sipBuilder->create();
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setDepartment($department)
            ->setSip($sip)->create();

        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->get(
            route('api.v1.calls.queues'),
            $this->headers()
        )
            ->assertOk()
            ->assertJson(['data' => [
                [
                    'id' => $queue->id,
                    'caller_name' => $queue->caller_name,
                    'caller_number' => $queue->caller_num,
                    'connected_name' => $queue->connected_name,
                    'connected_number' => $queue->connected_num,
                    'wait' => $queue->wait,
                    'status' => $queue->status,
                    'serial_number' => $queue->serial_number,
                    'case_id' => $queue->case_id,
                    'comment' => $queue->comment,
                    'connected_at' => $queue->connected_at,
                    'called_at' => $queue->called_at,
                    'type' => $queue->type,
                    'department' => [
                        'id' => $queue->department->id,
                    ],
                    'employee' => [
                        'id' => $queue->employee->id,
                    ]
                ]
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function get_empty_list()
    {
        $this->get(
            route('api.v1.calls.queues'),
            $this->headers()
        )
            ->assertOk()
            ->assertJsonStructure(['data' => []])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function wrong_auth_token()
    {
        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $res = $this->get(
            route('api.v1.calls.queues'),
            $headers
        )
            ->assertStatus(401)
        ;

        self::assertApiMsgError($res, 'Bad authorization token');
    }

    /** @test */
    public function without_auth_token()
    {
        $res = $this->get(
            route('api.v1.calls.queues')
        )
            ->assertStatus(401)
        ;

        self::assertApiMsgError($res, 'Missing authorization header');
    }
}

