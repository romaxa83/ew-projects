<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Enums\Orders\ActivityType;
use App\Models\Communications\CommunicationRecord;
use App\Models\Order;
use App\Services\Communications\FormatterService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\ActivityBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\SourceBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class OrderActivityTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected SourceBuilder $sourceBuilder;
    protected OrderBuilder $orderBuilder;
    protected ActivityBuilder $activityBuilder;
    protected StatusBuilder $statusBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->activityBuilder = resolve(ActivityBuilder::class);
        $this->statusBuilder = resolve(StatusBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->sourceBuilder = resolve(SourceBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->conversationFavoritesBuilder = resolve(ConversationFavoritesBuilder::class);
        $this->service = resolve(FormatterService::class);


        parent::setUp();
    }

    /** @test */
    public function one_simple_as_status_type()
    {
        $date = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $oldStatus = $this->statusBuilder->create();
        $newStatus = $this->statusBuilder->create();

        /** @var $entity Order\Activity */
        $entity = $this->activityBuilder
            ->type(ActivityType::Status)
            ->order($order)
            ->miscs([
                'from' => $oldStatus->id,
                'to' => $newStatus->id,
            ])
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof Order\Activity);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['timestamp'], $date->timestamp);
        $this->assertEquals($rec['update'], [
            'from' => [
                'title' => $oldStatus->title,
                'color' => $oldStatus->color,
            ],
            'to' => [
                'title' => $newStatus->title,
                'color' => $newStatus->color,
            ],
        ]);
    }

    /** @test */
    public function one_simple_as_user_type()
    {
        $date = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $oldUser = $this->userBuilder->create();
        $newUser = $this->userBuilder->create();

        /** @var $entity Order\Activity */
        $entity = $this->activityBuilder
            ->type(ActivityType::User)
            ->order($order)
            ->miscs([
                'from' => $oldUser->id,
                'to' => $newUser->id,
            ])
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['update'], [
            'from' => [
                'title' => $oldUser->name,
            ],
            'to' => [
                'title' => $newUser->name,
            ],
        ]);
    }

    /** @test */
    public function one_simple_as_division_type()
    {
        $date = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $oldDiv = $this->divisionBuilder->create();
        $newDiv = $this->divisionBuilder->create();

        /** @var $entity Order\Activity */
        $entity = $this->activityBuilder
            ->type(ActivityType::Division)
            ->order($order)
            ->miscs([
                'from' => $oldDiv->id,
                'to' => $newDiv->id,
            ])
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['update'], [
            'from' => [
                'title' => $oldDiv->title,
            ],
            'to' => [
                'title' => $newDiv->title,
            ],
        ]);
    }

    /** @test */
    public function one_simple_as_source_type()
    {
        $date = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $oldDiv = $this->sourceBuilder->create();
        $newDiv = $this->sourceBuilder->create();

        /** @var $entity Order\Activity */
        $entity = $this->activityBuilder
            ->type(ActivityType::Source)
            ->order($order)
            ->miscs([
                'from' => $oldDiv->id,
                'to' => $newDiv->id,
            ])
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['update'], [
            'from' => [
                'title' => $oldDiv->title,
            ],
            'to' => [
                'title' => $newDiv->title,
            ],
        ]);
    }

    /** @test */
    public function one_simple_as_email_type()
    {
        $date = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $entity Order\Activity */
        $entity = $this->activityBuilder
            ->type(ActivityType::Source)
            ->order($order)
            ->miscs([
                'employee_id' => null,
                'events' => [],
                'template_id' => 12,
                'text' => 'Notify work assign',
                'to' => 'soltasuk@gmail.com',
                'work_id' => 12,
            ])
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['update'], [
            'from' => [
                'title' => "",
            ],
            'to' => [
                'title' => "",
            ],
        ]);
    }
}


