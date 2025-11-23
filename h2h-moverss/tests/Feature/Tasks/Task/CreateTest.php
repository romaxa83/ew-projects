<?php

namespace Tests\Feature\Tasks\Task;

use App\Enums\Communications\Type;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Order;
use App\Models\Tasks\Task;
use App\Services\Communications\FormatterService;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create_with_order_check_communication()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $executor User */
        $executor = $this->userBuilder->create();

        $now = CarbonImmutable::now();

        $data = [
            'record' => [
                'title' => null,
                'description' => null,
                'executor_id' => $executor->id,
                'due_date' => $now->addDay()->format('Y-m-d H:i:s'),
                'order_id' => $order->id,
                'subscribers' => [],
                'priority' => 1,
                'notify_subscribers' => null,
                'notify_holder' => null,
                'miscs' => []
            ],
            'orderID' => $order->id,
            'returnFormat' => 'communicationPanel'

        ];

        $res = $this->post(route('tasks.create'), $data)
            ->assertJsonStructure([
                'success',
                'record' => [
                    'type',
                    'datetime',
                    'uid',
                    'timestamp',
                    'item' => [
                        'executor_id',
                        'title',
                        'description',
                        'priority',
                        'order_id',
                        'user_id',
                        'division_id',
                        'due_date',
                        'status_id',
                        'updated_at',
                        'created_at',
                        'id',
                        'subscribers',
                        'type',
                        'author' => [
                            'id',
                            'name',
                            'email',
                        ],
                        'executor' => [
                            'id',
                            'name',
                            'email',
                        ],
                    ]
                ]
            ])
            ->json('record')
        ;

        /** @var $task Task */
        $task =Task::find($res['item']['id']);

        $this->assertEquals($res['type'], FormatterService::getType($task));
        $this->assertEquals($res['uid'], FormatterService::getUid($task));

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->entity_id, $task->id);
        $this->assertEquals($rec->entity_type, Task::MORPH_NAME);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertTrue($rec->is_answered);
        $this->assertNull($rec->channel_contact);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->sort_at, $task->created_at);
    }

    /** @test */
    public function create_without_order()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $executor User */
        $executor = $this->userBuilder->create();

        $now = CarbonImmutable::now();

        $data = [
            'record' => [
                'title' => null,
                'description' => null,
                'executor_id' => $executor->id,
                'due_date' => $now->addDay()->format('Y-m-d H:i:s'),
                'order_id' => null,
                'subscribers' => [],
                'priority' => 1,
                'notify_subscribers' => null,
                'notify_holder' => null,
                'miscs' => []
            ],
            'orderID' => null,
            'returnFormat' => 'communicationPanel'

        ];

        $res = $this->post(route('tasks.create'), $data)
            ->json('record');

        /** @var $task Task */
        $task = Task::find($res['item']['id']);

        $this->assertEquals($res['type'], FormatterService::getType($task));
        $this->assertEquals($res['uid'], FormatterService::getUid($task));

        /** @var $rec CommunicationRecord */

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->entity_id, $task->id);
        $this->assertEquals($rec->entity_type, Task::MORPH_NAME);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertTrue($rec->is_answered);
        $this->assertNull($rec->channel_contact);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->sort_at, $task->created_at);
    }
}
