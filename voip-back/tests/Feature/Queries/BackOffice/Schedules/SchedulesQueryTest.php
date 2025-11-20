<?php

namespace Tests\Feature\Queries\BackOffice\Schedules;

use App\GraphQL\Queries\BackOffice;
use App\Models\Schedules\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Schedules\AdditionDayBuilder;
use Tests\TestCase;

class SchedulesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Schedules\SchedulesQuery::NAME;

    protected AdditionDayBuilder $additionDayBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->additionDayBuilder = resolve(AdditionDayBuilder::class);
    }

    /** @test */
    public function success_list(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Schedule */
        $model = Schedule::all()->first();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $model->id,
                            'days' => [
                                [
                                    'name' => $model->days[0]->name,
                                    'start_work_time' => $model->days[0]->start_work_time,
                                    'end_work_time' => $model->days[0]->end_work_time,
                                    'sort' => $model->days[0]->sort,
                                    'active' => $model->days[0]->active,
                                ],
                                [
                                    'name' => $model->days[1]->name,
                                    'start_work_time' => $model->days[1]->start_work_time,
                                    'end_work_time' => $model->days[1]->end_work_time,
                                    'sort' => $model->days[1]->sort,
                                    'active' => $model->days[1]->active,
                                ],
                                [
                                    'name' => $model->days[2]->name,
                                    'start_work_time' => $model->days[2]->start_work_time,
                                    'end_work_time' => $model->days[2]->end_work_time,
                                    'sort' => $model->days[2]->sort,
                                    'active' => $model->days[2]->active,
                                ],
                                [
                                    'name' => $model->days[3]->name,
                                    'start_work_time' => $model->days[3]->start_work_time,
                                    'end_work_time' => $model->days[3]->end_work_time,
                                    'sort' => $model->days[3]->sort,
                                    'active' => $model->days[3]->active,
                                ],
                                [
                                    'name' => $model->days[4]->name,
                                    'start_work_time' => $model->days[4]->start_work_time,
                                    'end_work_time' => $model->days[4]->end_work_time,
                                    'sort' => $model->days[4]->sort,
                                    'active' => $model->days[4]->active,
                                ],
                                [
                                    'name' => $model->days[5]->name,
                                    'start_work_time' => $model->days[5]->start_work_time,
                                    'end_work_time' => $model->days[5]->end_work_time,
                                    'sort' => $model->days[5]->sort,
                                    'active' => $model->days[5]->active,
                                ],
                                [
                                    'name' => $model->days[6]->name,
                                    'start_work_time' => $model->days[6]->start_work_time,
                                    'end_work_time' => $model->days[6]->end_work_time,
                                    'sort' => $model->days[6]->sort,
                                    'active' => $model->days[6]->active,
                                ]
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . self::QUERY)
            ->assertJsonCount(7, 'data.' . self::QUERY . '.0.days')
            ->assertJsonCount(0, 'data.' . self::QUERY . '.0.additional_days')
        ;
    }

    /** @test */
    public function success_list_with_additions(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Schedule */
        $model = Schedule::all()->first();

        $a_1 = $this->additionDayBuilder->setSchedule($model)->create();
        $a_2 = $this->additionDayBuilder->setSchedule($model)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $model->id,
                            'additional_days' => [
                                [
                                    'start_at' => $model->additionalDays[0]->start_at,
                                    'end_at' => $model->additionalDays[0]->end_at,
                                ],
                                [
                                    'start_at' => $model->additionalDays[1]->start_at,
                                    'end_at' => $model->additionalDays[1]->end_at,
                                ],
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . self::QUERY)
            ->assertJsonCount(7, 'data.' . self::QUERY . '.0.days')
            ->assertJsonCount(2, 'data.' . self::QUERY . '.0.additional_days')
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    id
                    days {
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
            self::QUERY
        );
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_auth(): void
    {
        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertUnauthorized($res);
    }
}

