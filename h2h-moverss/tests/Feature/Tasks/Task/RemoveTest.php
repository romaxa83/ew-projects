<?php

namespace Tests\Feature\Tasks\Task;

use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Tasks\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Tasks\StatusBuilder;
use Tests\Builders\Tasks\TaskBuilder;
use Tests\TestCase;

class RemoveTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected TaskBuilder $taskBuilder;
    protected StatusBuilder $statusBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->taskBuilder = resolve(TaskBuilder::class);
        $this->statusBuilder = resolve(StatusBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function remove_task_check_communication_record()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $task Task */
        $task = $this->taskBuilder->author($user)->create();

        $rec = $this->communicationRecordBuilder
            ->entity($task)
            ->create();
        $recId = $rec->id;
        $taskId = $task->id;

        $this->post(route('tasks.remove'), [
            'id' => $task->id,
        ])
            ->assertJson([
                'success' => true
            ])
        ;

        $this->assertNull(Task::find($taskId));
        $this->assertTrue(Task::query()->withTrashed()->where('id',$taskId)->exists());
        $this->assertNull(CommunicationRecord::find($recId));
    }

    /** @test */
    public function fail_remove_task_not_author()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $task Task */
        $task = $this->taskBuilder->create();
        $taskId = $task->id;

        $this->post(route('tasks.remove'), [
            'id' => $task->id,
        ])
            ->assertJson([
                'success' => false
            ])
        ;

        $this->assertNotNull(Task::find($taskId));
    }

    /** @test */
    public function fail_remove_task_as_completed()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        $status = $this->statusBuilder->id(3)->create();

        /** @var $task Task */
        $task = $this->taskBuilder->author($user)
            ->status($status)->create();
        $taskId = $task->id;

        $this->post(route('tasks.remove'), [
            'id' => $task->id,
        ])
            ->assertJson([
                'success' => false
            ])
        ;

        $this->assertNotNull(Task::find($taskId));
    }
}
