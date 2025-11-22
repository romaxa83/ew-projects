<?php

namespace Tests\Unit\Listeners\Commercial;

use App\Events\Commercial\SendCommercialProjectToOnec;
use App\Listeners\Commercial\SendCommercialProjectToOnecListener;
use App\Models\Commercial\CommercialProject;
use App\Models\Request\Request;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class SendCommercialProjectToOnecListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $projectBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
    }

    /** @test */
    public function success_create()
    {
        /** @var $model CommercialProject */
        $model = $this->projectBuilder->create();

        $this->assertNull(Request::first());

        $event = new SendCommercialProjectToOnec($model);
        $listener = resolve(SendCommercialProjectToOnecListener::class);
        $listener->handle($event);

        $record = Request::first();

        $this->assertTrue($record->command->isCreateCommercialProject());
    }

    /** @test */
    public function success_update()
    {
        /** @var $model CommercialProject */
        $model = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->assertNull(Request::first());

        $event = new SendCommercialProjectToOnec($model);
        $listener = resolve(SendCommercialProjectToOnecListener::class);
        $listener->handle($event);

        $record = Request::first();

        $this->assertTrue($record->command->isUpdateCommercialProject());
    }

    /** @test */
    public function something_wrong()
    {
        /** @var $model CommercialProject */
        $model = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->mock(RequestService::class, function(MockInterface $mock){
            $mock->shouldReceive("updateCommercialProject")
                ->andThrows(\Exception::class, "some exception message");
        });

        $event = new SendCommercialProjectToOnec($model);
        $listener = resolve(SendCommercialProjectToOnecListener::class);

        $this->expectException(TranslatedException::class);
        $this->expectExceptionMessage("some exception message");
        $this->expectExceptionCode(502);

        $listener->handle($event);
    }

}
