<?php

namespace Tests\Feature\Mutations\BackOffice\Musics;

use App\Enums\Formats\DayEnum;
use App\GraphQL\Mutations\BackOffice;
use App\Models\Departments\Department;
use App\Models\Schedules\Schedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;

class CreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected array $data;

    protected DepartmentBuilder $departmentBuilder;
    protected MusicBuilder $musicBuilder;

    public const MUTATION = BackOffice\Musics\MusicCreateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->musicBuilder = resolve(MusicBuilder::class);

        $this->data = [
            'interval' => 20
        ];
    }

    /** @test */
    public function success_create(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->data['active'] = 'true';
        $this->data['department_id'] = $department->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'interval' => data_get($this->data, 'interval'),
                        'active' => true,
                        'department' => [
                            'id' => $department->id
                        ],
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function fail_create_is_hold_state(): void
    {
        /** @var $model Schedule */
        $model = Schedule::all()->first();
        $scheduleMonday = $model->days()->where('name', DayEnum::MONDAY)->first();
        $scheduleMonday->end_work_time = '18:00';
        $scheduleMonday->save();

        // monday
        $date = new CarbonImmutable('2022-10-03 14:30:00');
        CarbonImmutable::setTestNow($date);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->data['active'] = 'true';
        $this->data['department_id'] = $department->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        self::assertErrorMessage($res, __('exceptions.music.hold'));
    }

    /** @test */
    public function fail_department_not_unique(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->musicBuilder->department($department)->create();

        $this->data['active'] = 'true';
        $this->data['department_id'] = $department->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $field = 'input.department_id';
        $this->assertResponseHasValidationMessage($res, $field, [
            __('validation.unique', ['attribute' => remove_underscore($field)])
        ]);

    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->data['active'] = 'true';
        $this->data['department_id'] = $department->id;

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

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->data['active'] = 'true';
        $this->data['department_id'] = $department->id;

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
            data_get($data, 'interval'),
            data_get($data, 'active'),
            data_get($data, 'department_id'),
        );
    }
}
