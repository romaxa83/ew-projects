<?php

namespace Tests\Feature\Mutations\BackOffice\Departments;

use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Events\Queue\QueueUpdateOrCreateEvent;
use App\IPTelephony\Events\QueueMember\QueueMemberUpdateNameEvent;
use App\IPTelephony\Listeners\Queue\QueueUpdateOrInsertListener;
use App\IPTelephony\Listeners\QueueMember\QueueMemberUpdateNameListener;
use App\Models\Departments\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\TestCase;

class UpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Departments\DepartmentUpdateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->departmentBuilder = resolve(DepartmentBuilder::class);

        $this->data = [
            'name' => $this->faker->word
        ];
    }

    /** @test */
    public function success_update(): void
    {
        Event::fake([
            QueueUpdateOrCreateEvent::class,
            QueueMemberUpdateNameEvent::class
        ]);

        $this->loginAsSuperAdmin();

        $num = 99889;
        /** @var $model Department */
        $model = $this->departmentBuilder->setData(['num' => $num])->create();

        $data = $this->data;
        $data['id'] = $model->id;

        $this->assertNotEquals($model->name, data_get($data, 'name'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'name' => data_get($data, 'name')
                    ],
                ]
            ])
        ;

        Event::assertDispatched(fn (QueueMemberUpdateNameEvent $event) =>
            $event->getOldName() === $model->name
            && $event->getNewName() === data_get($data, 'name')
        );
        Event::assertListening(
            QueueMemberUpdateNameEvent::class,
            QueueMemberUpdateNameListener::class)
        ;

        $model->refresh();
        $this->assertEquals($model->num, $num);

        Event::assertDispatched(fn (QueueUpdateOrCreateEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueUpdateOrCreateEvent::class,
            QueueUpdateOrInsertListener::class)
        ;
    }

    /** @test */
    public function success_update_not_name(): void
    {
        Event::fake([
            QueueUpdateOrCreateEvent::class,
            QueueMemberUpdateNameEvent::class
        ]);

        $this->loginAsSuperAdmin();

        $num = 99889;
        /** @var $model Department */
        $model = $this->departmentBuilder->setData(['num' => $num])->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['name'] = $model->name;

        $this->assertEquals($model->name, data_get($data, 'name'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'name' => data_get($data, 'name')
                    ],
                ]
            ])
        ;

        Event::assertNotDispatched(QueueMemberUpdateNameEvent::class);

        $model->refresh();
        $this->assertEquals($model->num, $num);

        Event::assertDispatched(fn (QueueUpdateOrCreateEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueUpdateOrCreateEvent::class,
            QueueUpdateOrInsertListener::class)
        ;
    }

    /** @test */
    public function fail_not_uniq_name(): void
    {
        Event::fake([QueueUpdateOrCreateEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $model Department */
        $model = $this->departmentBuilder->create();
        $model_another = $this->departmentBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['name'] = $model_another->name;

        $this->assertNotEquals($model->name, data_get($data, 'name'));

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $field = 'input.name';
        $this->assertResponseHasValidationMessage($res, $field, [
            __('validation.unique', ['attribute' => $field])
        ]);

        Event::assertNotDispatched(QueueUpdateOrCreateEvent::class);
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Department */
        $model = $this->departmentBuilder->create();
        $data = $this->data;
        $data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $model Department */
        $model = $this->departmentBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $this->assertPermission($res);
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    input: {
                        name: "%s"
                    },
                ) {
                    id
                    sort
                    name
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'name'),
        );
    }
}
