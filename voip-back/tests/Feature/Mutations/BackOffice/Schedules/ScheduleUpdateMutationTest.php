<?php

namespace Tests\Feature\Mutations\BackOffice\Schedules;

use App\GraphQL\Mutations\BackOffice;
use App\Models\Schedules\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScheduleUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    protected array $data;

    public const MUTATION = BackOffice\Schedules\ScheduleUpdateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_update(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Schedule */
        $model = Schedule::all()->first();

        $data = [
            'days' => [
                [
                    'id' => $model->days[0]->id,
                    'start_work_time' => '1:00',
                    'end_work_time' => '2:00',
                ],
                [
                    'id' => $model->days[1]->id,
                    'start_work_time' => '3:00',
                    'end_work_time' => '4:00',
                ]
            ],
            'additional_days' => [
                [
                    'start_at' => '2023-12-12'
                ],
                [
                    'start_at' => '2023-12-02',
                    'end_at' => '2023-12-03'
                ]
            ]
        ];

        $this->assertNotEquals($model->days[0]->start_work_time, data_get($data, 'days.0.start_work_time'));
        $this->assertNotEquals($model->days[0]->end_work_time, data_get($data, 'days.0.end_work_time'));
        $this->assertTrue($model->days[0]->active);

        $this->assertNotEquals($model->days[1]->start_work_time, data_get($data, 'days.1.start_work_time'));
        $this->assertNotEquals($model->days[1]->end_work_time, data_get($data, 'days.1.end_work_time'));
        $this->assertTrue($model->days[1]->active);

        $this->assertEmpty($model->additionalDays);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'days' => [
                                [
                                    'name' => $model->days[0]->name,
                                    'start_work_time' => data_get($data, 'days.0.start_work_time'),
                                    'end_work_time' => data_get($data, 'days.0.end_work_time'),
                                    'active' => false,
                                ],
                                [
                                    'name' => $model->days[1]->name,
                                    'start_work_time' => data_get($data, 'days.1.start_work_time'),
                                    'end_work_time' => data_get($data, 'days.1.end_work_time'),
                                    'active' => false,
                                ],
                                [
                                    'name' => $model->days[2]->name,
                                    'start_work_time' => $model->days[2]->start_work_time,
                                    'end_work_time' => $model->days[2]->end_work_time,
                                    'active' => $model->days[2]->active,
                                ],
                                [
                                    'name' => $model->days[3]->name,
                                    'start_work_time' => $model->days[3]->start_work_time,
                                    'end_work_time' => $model->days[3]->end_work_time,
                                    'active' => $model->days[3]->active,
                                ],
                                [
                                    'name' => $model->days[4]->name,
                                    'start_work_time' => $model->days[4]->start_work_time,
                                    'end_work_time' => $model->days[4]->end_work_time,
                                    'active' => $model->days[4]->active,
                                ],
                                [
                                    'name' => $model->days[5]->name,
                                    'start_work_time' => $model->days[5]->start_work_time,
                                    'end_work_time' => $model->days[5]->end_work_time,
                                    'active' => $model->days[5]->active,
                                ],
                                [
                                    'name' => $model->days[6]->name,
                                    'start_work_time' => $model->days[6]->start_work_time,
                                    'end_work_time' => $model->days[6]->end_work_time,
//                                    'active' => $model->days[6]->active,
                                ]
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . self::MUTATION)
            ->assertJsonCount(7, 'data.' . self::MUTATION . '.0.days')
            ->assertJsonCount(2, 'data.' . self::MUTATION . '.0.additional_days')
        ;
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    days: [
                        {
                            id: %s
                            start_work_time: "%s"
                            end_work_time: "%s"
                            active: false
                        }
                        {
                            id: %s
                            start_work_time: "%s"
                            end_work_time: "%s"
                            active: false
                        }
                    ]
                    additional_days: [
                        {
                            start_at: "%s"
                        }
                        {
                            start_at: "%s"
                            end_at: "%s"
                        }
                    ]
                ) {
                    days {
                        id
                        name
                        start_work_time
                        end_work_time
                        sort
                        active
                    }
                    additional_days {
                        start_at
                        end_at
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'days.0.id'),
            data_get($data, 'days.0.start_work_time'),
            data_get($data, 'days.0.end_work_time'),
            data_get($data, 'days.1.id'),
            data_get($data, 'days.1.start_work_time'),
            data_get($data, 'days.1.end_work_time'),
            data_get($data, 'additional_days.0.start_at'),
            data_get($data, 'additional_days.1.start_at'),
            data_get($data, 'additional_days.1.end_at'),
        );
    }
}
