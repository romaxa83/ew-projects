<?php

namespace Tests\Feature\Mutations\BackOffice\Musics;

use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Events\Queue\QueueDeleteMusicEvent;
use App\IPTelephony\Events\Queue\QueueUpdateMusicEvent;
use App\IPTelephony\Listeners\Queue\QueueDeleteMusicListener;
use App\IPTelephony\Listeners\Queue\QueueUpdateMusicListener;
use App\Models\Departments\Department;
use App\Models\Musics\Music;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;

class UpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;
    protected MusicBuilder $musicBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Musics\MusicUpdateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->musicBuilder = resolve(MusicBuilder::class);

        $this->data = [
            'interval' => 30,
            'active' => 'false'
        ];
    }

    /** @test */
    public function success_update(): void
    {
        Event::fake([
            QueueUpdateMusicEvent::class,
            QueueDeleteMusicEvent::class,
        ]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Music */
        $model = $this->musicBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;

        $this->assertNotEquals($model->interval, data_get($data, 'interval'));
        $this->assertNotEquals($model->department_id, $department->id);
        $this->assertTrue($model->active);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'interval' => data_get($data, 'interval'),
                        'active' => false,
                        'department' => [
                            'id' => $department->id
                        ]
                    ],
                ]
            ])
        ;

        Event::assertNotDispatched(QueueUpdateMusicEvent::class);
        Event::assertNotDispatched(QueueDeleteMusicEvent::class);
    }

    /** @test */
    public function success_update_model_has_media_active_false(): void
    {
        Event::fake([
            QueueDeleteMusicEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Music */
        $model = $this->musicBuilder->withRecord()->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;

        $this->assertNotEquals($model->interval, data_get($data, 'interval'));
        $this->assertNotEquals($model->department_id, $department->id);
        $this->assertTrue($model->active);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'interval' => data_get($data, 'interval'),
                        'active' => false,
                        'department' => [
                            'id' => $department->id
                        ]
                    ],
                ]
            ])
        ;

        Event::assertDispatched(fn (QueueDeleteMusicEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueDeleteMusicEvent::class,
            QueueDeleteMusicListener::class)
        ;
    }

    /** @test */
    public function success_update_not_change_fields(): void
    {
        Event::fake([
            QueueUpdateMusicEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->withRecord()->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $model->department_id;
        $data['active'] = 'true';
        $data['interval'] = $model->interval;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'interval' => $model->interval,
                        'active' => $model->active,
                        'department' => [
                            'id' => $model->department_id
                        ]
                    ],
                ]
            ])
        ;

        Event::assertDispatched(fn (QueueUpdateMusicEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueUpdateMusicEvent::class,
            QueueUpdateMusicListener::class)
        ;
    }

    /** @test */
    public function fail_not_uniq_department(): void
    {
        $this->loginAsSuperAdmin();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Music */
        $model = $this->musicBuilder->create();
        $modelAnother = $this->musicBuilder->department($department)->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $field = 'input.department_id';
        $this->assertResponseHasValidationMessage($res, $field, [
            __('validation.unique', ['attribute' => remove_underscore($field)])
        ]);
    }

    /** @test */
    public function fail_update_is_hold_state(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Music */
        $model = $this->musicBuilder->hold()->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;

        $this->assertTrue($model->isHoldState());

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        self::assertErrorMessage($res, __('exceptions.music.hold'));
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Music */
        $model = $this->musicBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $model->department_id;

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

        /** @var $model Music */
        $model = $this->musicBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $model->department_id;

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
                        interval: %s
                        active: %s
                        department_id: %s
                    },
                ) {
                    id
                    interval
                    active
                    department {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'interval'),
            data_get($data, 'active'),
            data_get($data, 'department_id'),
        );
    }
}
