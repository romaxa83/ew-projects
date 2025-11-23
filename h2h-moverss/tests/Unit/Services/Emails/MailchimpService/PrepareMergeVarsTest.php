<?php

namespace Tests\Unit\Services\Emails\MailchimpService;

use App\Models\Order;
use App\Models\Settings\WaypointFlights;
use App\Services\Emails\MailchimpService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\WaypointBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\Builders\Settings\WaypointFlightsBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Works\WorkTypeBuilder;
use Tests\TestCase;

class PrepareMergeVarsTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected UserBuilder $userBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected ClientBuilder $clientBuilder;
    protected WaypointBuilder $waypointBuilder;
    protected WorkBuilder $workBuilder;
    protected WorkTypeBuilder $workTypeBuilder;
    protected WaypointFlightsBuilder $waypointFlightsBuilder;
    protected MailchimpService $mailchimpService;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->waypointBuilder = resolve(WaypointBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->workTypeBuilder = resolve(WorkTypeBuilder::class);
        $this->waypointFlightsBuilder = resolve(WaypointFlightsBuilder::class);
        $this->mailchimpService = resolve(MailchimpService::class);
    }

    /** @test */
    public function prepareMergeVars_returns_correct_structure()
    {
        // Create a user (manager)
        /** @var \App\User $manager */
        $manager = $this->userBuilder
            ->setData([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->create();

        // Create a client
        /** @var \App\Models\Client $client */
        $client = $this->clientBuilder
            ->setData([
                'name' => 'Jane',
                'lname' => 'Smith'
            ])
            ->create();

        // Create an order with the manager and client
        /** @var Order $order */
        $order = $this->orderBuilder
            ->manager($manager)
            ->setData([
                'client_id' => $client->id,
                'hash' => 'test-hash-123'
            ])
            ->create();

        // Call the method
        $result = $this->mailchimpService->prepareMergeVars($order);

        // Assert the structure of the result
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check that all required merge vars are present
        $expectedVars = [
            'CLIENT_NAME',
            'CLIENT_FIRST_NAME',
            'CLIENT_LAST_NAME',
            'CUSTOMER_PAGE_URL',
            'MANAGER_CURRENT_NAME',
            'MANAGER_NAME',
            'MANAGER_EMAIL',
            'ORDER_ID',
            'WAYPOINT_ORIGIN_FIRST_ADDRESS',
            'WAYPOINT_ORIGIN_ADDRESS',
            'WAYPOINT_ORIGIN_HAS_ELEVATOR',
            'WAYPOINT_PICKUP_ADDRESS',
            'WAYPOINT_DESTINATION_ADDRESS',
            'WAYPOINT_ORIGIN_STAIRS_FLIGHTS',
            'WAYPOINT_PICKUP_STAIRS_FLIGHTS',
            'WAYPOINT_DESTINATION_STAIRS_FLIGHTS',
            'WAYPOINT_DESTINATION_HAS_ELEVATOR',
            'MOVING_FIRST_DATETIME'
        ];

        foreach ($result as $mergeVar) {
            $this->assertArrayHasKey('name', $mergeVar);
            $this->assertArrayHasKey('content', $mergeVar);
            $this->assertContains($mergeVar['name'], $expectedVars);
        }

        // Check that the number of merge vars matches the expected count
        $this->assertCount(count($expectedVars), $result);
    }

    /** @test */
    public function prepareMergeVars_includes_client_information()
    {
        // Create a user (manager)
        /** @var \App\User $manager */
        $manager = $this->userBuilder
            ->setData([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->create();

        // Create a client
        /** @var \App\Models\Client $client */
        $client = $this->clientBuilder
            ->setData([
                'name' => 'Jane',
                'lname' => 'Smith'
            ])
            ->create();

        // Create an order with the manager and client
        /** @var Order $order */
        $order = $this->orderBuilder
            ->manager($manager)
            ->setData([
                'client_id' => $client->id,
                'hash' => 'test-hash-123'
            ])
            ->create();

        // Call the method
        $result = $this->mailchimpService->prepareMergeVars($order);

        // Find client-related merge vars
        $clientName = $this->findMergeVarContent($result, 'CLIENT_NAME');
        $clientFirstName = $this->findMergeVarContent($result, 'CLIENT_FIRST_NAME');
        $clientLastName = $this->findMergeVarContent($result, 'CLIENT_LAST_NAME');
        $customerPageUrl = $this->findMergeVarContent($result, 'CUSTOMER_PAGE_URL');

        // Assert client information is correct
        $this->assertEquals('Jane Smith', $clientName);
        $this->assertEquals('Jane', $clientFirstName);
        $this->assertEquals('Smith', $clientLastName);
        $this->assertStringContainsString('/customer/order/test-hash-123', $customerPageUrl);
    }

    /** @test */
    public function prepareMergeVars_includes_manager_information()
    {
        // Create a user (manager)
        /** @var \App\User $manager */
        $manager = $this->userBuilder
            ->setData([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->create();

        // Create an order with the manager
        /** @var Order $order */
        $order = $this->orderBuilder
            ->manager($manager)
            ->create();

        // Call the method
        $result = $this->mailchimpService->prepareMergeVars($order);

        // Find manager-related merge vars
        $authManagerName = $this->findMergeVarContent($result, 'MANAGER_CURRENT_NAME');
        $managerName = $this->findMergeVarContent($result, 'MANAGER_NAME');
        $managerEmail = $this->findMergeVarContent($result, 'MANAGER_EMAIL');

        // Assert manager information is correct
        $this->assertEquals('John Doe', $managerName);
        $this->assertEquals('john.doe@example.com', $managerEmail);
        $this->assertEmpty($authManagerName);
    }

    /** @test */
    public function prepareMergeVars_includes_auth_manager()
    {
        $authUser = $this->userBuilder->create();
        $authEmployee = $this->employeeBuilder->user($authUser)->create();

        $this->loginUser($authUser);

        // Create a user (manager)
        /** @var \App\User $manager */
        $manager = $this->userBuilder
            ->setData([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->create();

        // Create an order with the manager
        /** @var Order $order */
        $order = $this->orderBuilder
            ->manager($manager)
            ->create();

        // Call the method
        $result = $this->mailchimpService->prepareMergeVars($order);

        // Find manager-related merge vars
        $authManagerName = $this->findMergeVarContent($result, 'MANAGER_CURRENT_NAME');
        $managerName = $this->findMergeVarContent($result, 'MANAGER_NAME');
        $managerEmail = $this->findMergeVarContent($result, 'MANAGER_EMAIL');

        // Assert manager information is correct
        $this->assertEquals('John Doe', $managerName);
        $this->assertEquals('john.doe@example.com', $managerEmail);
        $this->assertEquals($authEmployee->full_name, $authManagerName);
    }

    /** @test */
    public function prepareMergeVars_includes_waypoint_information()
    {
        // Create a user (manager)
        /** @var \App\User $manager */
        $manager = $this->userBuilder
            ->setData([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->create();

        // Create an order with the manager
        /** @var Order $order */
        $order = $this->orderBuilder
            ->manager($manager)
            ->create();

        // Create waypoint flights
        /** @var WaypointFlights $waypointFlights */
        $waypointFlights = $this->waypointFlightsBuilder
            ->setData([
                'title' => '3 flights'
            ])
            ->create();

        // Add pickup waypoint to the order
        $waypoint_pickup = $this->waypointBuilder
            ->order($order)
            ->setData([
                'type' => 'pickup',
                'address' => '123 Main St USA',
                'zip' => '12345',
                'has_elevator' => true,
                'flights_id' => $waypointFlights->id,
                'created_at' => now()->subDay(),
                'sort' => 1
            ])
            ->create();

        // Add destination waypoint to the order
        $waypoint_destination = $this->waypointBuilder
            ->order($order)
            ->setData([
                'type' => 'destination',
                'address' => '456 Oak Ave USA',
                'zip' => '67890',
                'has_elevator' => false,
                'flights_id' => $waypointFlights->id,
                'sort' => 2
            ])
            ->create();

        // Call the method
        $result = $this->mailchimpService->prepareMergeVars($order);

        // Find waypoint-related merge vars
        $originFirstAddress = $this->findMergeVarContent($result, 'WAYPOINT_ORIGIN_FIRST_ADDRESS');
        $originAddress = $this->findMergeVarContent($result, 'WAYPOINT_ORIGIN_ADDRESS');
        $pickupAddress = $this->findMergeVarContent($result, 'WAYPOINT_PICKUP_ADDRESS');
        $destinationAddress = $this->findMergeVarContent($result, 'WAYPOINT_DESTINATION_ADDRESS');
        $originHasElevator = $this->findMergeVarContent($result, 'WAYPOINT_ORIGIN_HAS_ELEVATOR');
        $destinationHasElevator = $this->findMergeVarContent($result, 'WAYPOINT_DESTINATION_HAS_ELEVATOR');
        $originStairsFlights = $this->findMergeVarContent($result, 'WAYPOINT_ORIGIN_STAIRS_FLIGHTS');
        $pickupStairsFlights = $this->findMergeVarContent($result, 'WAYPOINT_PICKUP_STAIRS_FLIGHTS');
        $destinationStairsFlights = $this->findMergeVarContent($result, 'WAYPOINT_DESTINATION_STAIRS_FLIGHTS');

        // Assert waypoint information is correct
        $this->assertEquals('123 Main St 12345', $originFirstAddress);
        $this->assertEquals('123 Main St 12345', $originAddress);
        $this->assertEquals('123 Main St 12345', $pickupAddress);
        $this->assertEquals('456 Oak Ave 67890', $destinationAddress);
        $this->assertEquals('yes', $originHasElevator);
        $this->assertEquals('no', $destinationHasElevator);
        $this->assertEquals('3 flights', $originStairsFlights);
        $this->assertEquals('3 flights', $pickupStairsFlights);

        $this->assertEquals($waypoint_destination->flights_id, $destinationStairsFlights);
    }

    /** @test */
    public function prepareMergeVars_includes_work_information()
    {
        // Create a user (manager)
        /** @var \App\User $manager */
        $manager = $this->userBuilder
            ->setData([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->create();

        // Create an order with the manager
        /** @var Order $order */
        $order = $this->orderBuilder
            ->manager($manager)
            ->create();

        // Create a work type
        /** @var \App\Models\Works\WorkType $workType */
        $workType = $this->workTypeBuilder
            ->setData([
                'id' => 1,
                'title' => 'Moving'
            ])
            ->create();

        // Create a work with the work type
        /** @var \App\Models\Order\Work $work */
        $work = $this->workBuilder
            ->order($order)
            ->setData([
                'start_date' => '2023-01-15',
                'start_time' => '09:00:00'
            ])
            ->create();

        // Attach the work type to the work
        $work->workTypes()->attach($workType->id);

        // Call the method
        $result = $this->mailchimpService->prepareMergeVars($order);

        // Find work-related merge vars
        $movingFirstDatetime = $this->findMergeVarContent($result, 'MOVING_FIRST_DATETIME');

        // Assert work information is correct
        $this->assertEquals('Jan 15, 2023 at 9:00 AM', $movingFirstDatetime);
    }

    /**
     * Helper method to find a merge var's content by name
     */
    private function findMergeVarContent(array $mergeVars, string $name): string
    {
        foreach ($mergeVars as $mergeVar) {
            if ($mergeVar['name'] === $name) {
                return $mergeVar['content'];
            }
        }
        return '';
    }
}
