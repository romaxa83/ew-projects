<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Order;
use App\Models\Tasks\Task;
use App\Services\Communications\FormatterService;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Tasks\TaskBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class TaskRecTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected TaskBuilder $taskBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected OrderBuilder $orderBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
        $this->taskBuilder = resolve(TaskBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->conversationFavoritesBuilder = resolve(ConversationFavoritesBuilder::class);
        $this->service = resolve(FormatterService::class);


        parent::setUp();
    }

    /** @test */
    public function one_simple()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $author User */
        $author = $this->userBuilder->create();
        /** @var $executor User */
        $executor = $this->userBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $entity Task */
        $entity = $this->taskBuilder
            ->order($order)
            ->author($author)
            ->executor($executor)
            ->due_date($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->division($division)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof Task);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['timestamp'], $date->timestamp);

        $this->assertEquals($rec['item']->author->id, $author->id);
        $this->assertEquals($rec['item']->author->name, $author->name);

        $this->assertEquals($rec['item']->executor->id, $executor->id);
        $this->assertEquals($rec['item']->executor->name, $executor->name);

        $this->assertEquals($rec['item']->type->id, $entity->type_id);

        $this->assertEquals($rec['item']->status->id, $entity->status_id);
    }
}


