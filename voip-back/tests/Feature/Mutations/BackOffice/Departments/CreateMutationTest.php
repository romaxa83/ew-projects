<?php

namespace Tests\Feature\Mutations\BackOffice\Departments;

use App\Events\Departments\DepartmentCreatedEvent;
use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Listeners\Queue\QueueInsertListener;
use App\Models\Departments\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\TestCase;

class CreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected array $data;

    protected DepartmentBuilder $departmentBuilder;

    public const MUTATION = BackOffice\Departments\DepartmentCreateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->departmentBuilder = resolve(DepartmentBuilder::class);

        $this->data = [
            'name' => $this->faker->word
        ];
    }

    /** @test */
    public function success_create(): void
    {
        Event::fake([DepartmentCreatedEvent::class]);

        $this->loginAsSuperAdmin();

        $id = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        'id',
                        'name',
                        'sort',
                        'active',
                        'created_at',
                        'updated_at',
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'name' => data_get($this->data, 'name'),
                        'has_queue_record' => false,
                    ],
                ]
            ])
            ->json('data.'.self::MUTATION.'.id')
        ;

        /** @var $model Department */
        $model = Department::find($id);

        $this->assertNotNull($model->guid);
        $this->assertNotNull($model->num);

        Event::assertDispatched(fn (DepartmentCreatedEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(DepartmentCreatedEvent::class, QueueInsertListener::class);
    }

    /** @test */
    public function fail_not_uniq_name(): void
    {
        Event::fake([DepartmentCreatedEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $model Department */
        $model = $this->departmentBuilder->create();

        $this->data['name'] = $model->name;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $field = 'input.name';
        $this->assertResponseHasValidationMessage($res, $field, [
            __('validation.unique', ['attribute' => $field])
        ]);

        Event::assertNotDispatched(DepartmentCreatedEvent::class);
    }

    /** @test */
    public function not_auth(): void
    {
        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
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
                    input: {
                        name: "%s"
                    },
                ) {
                    id
                    sort
                    name
                    active
                    has_queue_record
                    created_at
                    updated_at
                }
            }',
            self::MUTATION,
            data_get($data, 'name'),
        );
    }
}
