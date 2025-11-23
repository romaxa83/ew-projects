<?php

namespace Tests\Unit\Models\Orders\Order;

use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder as ClientEmailBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Orders\EstimateBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\WaypointBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ScopeMandrillTemplateVarsTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected UserBuilder $userBuilder;
    protected ClientBuilder $clientBuilder;
    protected ClientEmailBuilder $clientEmailBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected WaypointBuilder $waypointBuilder;
    protected WorkBuilder $workBuilder;
    protected EstimateBuilder $estimateBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->clientEmailBuilder = resolve(ClientEmailBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->waypointBuilder = resolve(WaypointBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->estimateBuilder = resolve(EstimateBuilder::class);
    }

    /** @test */
    public function scopeMandrillTemplateVars_loads_manager_with_employee_and_emails()
    {
        // Create a user (manager) linked to the employee
        /** @var \App\User $manager */
        $manager = $this->userBuilder
            ->setData([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->create();

        // Create an employee with emails
        /** @var \App\Models\Employee $employee */
        $employee = $this->employeeBuilder
            ->user($manager)
            ->setData([
                'name' => 'John',
                'l_name' => 'Doe'
            ])
            ->create();

        // Create an order with the manager
        /** @var Order $order */
        $order = $this->orderBuilder
            ->manager($manager)
            ->create();

        // Call the method
        $result = Order::mandrillTemplateVars()->find($order->id);

        // Assert that manager is loaded with the correct columns
        $this->assertNotNull($result->manager);
        $this->assertEquals($manager->id, $result->manager->id);
        $this->assertEquals($manager->name, $result->manager->name);
        $this->assertEquals($manager->email, $result->manager->email);

        // Assert that manager.employee is loaded with the correct columns
        $this->assertNotNull($result->manager->employee);
        $this->assertEquals($employee->id, $result->manager->employee->id);
        $this->assertEquals($employee->name, $result->manager->employee->name);
        $this->assertEquals($employee->l_name, $result->manager->employee->l_name);

        // Assert that manager.employee.emails is loaded and ordered correctly
        $this->assertNotNull($result->manager->employee->emails);
    }

    /** @test */
    public function scopeMandrillTemplateVars_loads_client_with_emails()
    {
        // Create a client
        /** @var \App\Models\Client $client */
        $client = $this->clientBuilder
            ->setData([
                'name' => 'Jane',
                'lname' => 'Smith'
            ])
            ->create();

        // Add emails to the client
        $this->clientEmailBuilder
            ->client($client)
            ->setData([
                'value' => 'jane.smith@example.com',
                'is_primary' => 1,
                'sort' => 1
            ])
            ->create();

        $this->clientEmailBuilder
            ->client($client)
            ->setData([
                'value' => 'jane.secondary@example.com',
                'is_primary' => 0,
                'sort' => 2
            ])
            ->create();

        // Create an order with the client
        /** @var Order $order */
        $order = $this->orderBuilder
            ->setData([
                'client_id' => $client->id
            ])
            ->create();

        // Call the method
        $result = Order::mandrillTemplateVars()->find($order->id);

        // Assert that client is loaded with the correct columns
        $this->assertNotNull($result->client);
        $this->assertEquals($client->id, $result->client->id);
        $this->assertEquals($client->name, $result->client->name);
        $this->assertEquals($client->lname, $result->client->lname);

        // Assert that client.emails is loaded and ordered correctly
        $this->assertNotNull($result->client->emails);
        $this->assertEquals(2, $result->client->emails->count());
        $this->assertEquals('jane.smith@example.com', $result->client->emails[0]->value);
        $this->assertEquals('jane.secondary@example.com', $result->client->emails[1]->value);
    }

    /** @test */
    public function scopeMandrillTemplateVars_loads_waypoints_ordered_by_sort()
    {
        // Create an order
        /** @var Order $order */
        $order = $this->orderBuilder->create();

        // Add waypoints to the order
        $waypoints_1 = $this->waypointBuilder
            ->order($order)
            ->setData([
                'sort' => 2
            ])
            ->create();

        $waypoints_2 =  $this->waypointBuilder
            ->order($order)
            ->setData([
                'sort' => 1
            ])
            ->create();

        // Call the method
        $result = Order::mandrillTemplateVars()->find($order->id);

        // Assert that waypoints are loaded and ordered by sort
        $this->assertNotNull($result->waypoints);
        $this->assertEquals(2, $result->waypoints->count());
        $this->assertEquals($waypoints_2->id, $result->waypoints[0]->id);
        $this->assertEquals($waypoints_1->id, $result->waypoints[1]->id);
    }

    /** @test */
    public function scopeMandrillTemplateVars_loads_works_with_workTypes_ordered_correctly()
    {
        // Create an order
        /** @var Order $order */
        $order = $this->orderBuilder->create();

        // Add works to the order
        /** @var \App\Models\Order\Work $work1 */
        $work1 = $this->workBuilder
            ->order($order)
            ->setData([
                'start_date' => now()->addDay()
            ])
            ->create();

        /** @var \App\Models\Order\Work $work2 */
        $work2 = $this->workBuilder
            ->order($order)
            ->setData([
                'start_date' => now()
            ])
            ->create();

        /** @var \App\Models\Order\Work $work3 */
        $work3 = $this->workBuilder
            ->order($order)
            ->setData([
                'start_date' => null
            ])
            ->create();

        // Call the method
        $result = Order::mandrillTemplateVars()->find($order->id);

        // Assert that works are loaded and ordered correctly
        $this->assertNotNull($result->works);
        $this->assertEquals(3, $result->works->count());

        // Works with null start_date should come first, then ordered by start_date ASC
        $this->assertEquals($work3->id, $result->works[2]->id);
        $this->assertEquals($work2->id, $result->works[0]->id);
        $this->assertEquals($work1->id, $result->works[1]->id);

        // Assert that workTypes are loaded for each work
        $this->assertNotNull($result->works[0]->workTypes);
    }
}
