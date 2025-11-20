<?php

namespace Tests\Feature\Queries\BackOffice\Sips;

use App\GraphQL\Queries\BackOffice;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class SipsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Sips\SipsQuery::NAME;

    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        $m_1 = $this->sipBuilder->create();
        $m_2 = $this->sipBuilder->create();
        $m_3 = $this->sipBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_3->id],
                            ['id' => $m_2->id],
                            ['id' => $m_1->id],
                        ],
                        'meta' => [
                            'total' => 3
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_paginator_as_employee(): void
    {
        $this->loginAsEmployee();

        $m_1 = $this->sipBuilder->create();
        $m_2 = $this->sipBuilder->create();
        $m_3 = $this->sipBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_3->id],
                            ['id' => $m_2->id],
                            ['id' => $m_1->id],
                        ],
                        'meta' => [
                            'total' => 3
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_with_page(): void
    {
        $this->loginAsSuperAdmin();

        $this->sipBuilder->create();
        $this->sipBuilder->create();
        $this->sipBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithPage(2)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'meta' => [
                            'total' => 3,
                            'per_page' => 10,
                            'current_page' => 2,
                            'from' => null,
                            'to' => null,
                            'last_page' => 1,
                            'has_more_pages' => false,
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrWithPage($page): string
    {
        return sprintf(
            '
            {
                %s (page: %s) {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::QUERY,
            $page
        );
    }

    /** @test */
    public function success_with_per_page(): void
    {
        $this->loginAsSuperAdmin();

        $this->sipBuilder->create();
        $this->sipBuilder->create();
        $this->sipBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithPerPage(2)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'meta' => [
                            'total' => 3,
                            'per_page' => 2,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 2,
                            'last_page' => 2,
                            'has_more_pages' => true,
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrWithPerPage($perPage): string
    {
        return sprintf(
            '
            {
                %s (per_page: %s) {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::QUERY,
            $perPage
        );
    }

    /** @test */
    public function success_empty(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'meta' => [
                            'total' => 0
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::QUERY
        );
    }

    /** @test */
    public function filter_by_id(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        $this->sipBuilder->create();
        $this->sipBuilder->create();

        $employee = $this->employeeBuilder->setSip($model)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $model->id,
                                'number' => $model->number,
                                'employee' => [
                                    'id' => $employee->id
                                ],
                            ]
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrById($value): string
    {
        return sprintf(
            '
            {
                %s (id: "%s"){
                    data {
                        id
                        number
                        employee {
                            id
                        }
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $value
        );
    }

    /** @test */
    public function filter_by_has_employee(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        $model_2 = $this->sipBuilder->create();
        $model_3 = $this->sipBuilder->create();
        $model_4 = $this->sipBuilder->create();

        $this->employeeBuilder->setSip($model)->create();
        $this->employeeBuilder->setSip($model_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByHasEmployee('true')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $model_3->id],
                            ['id' => $model->id]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function filter_by_doesnt_have_employee(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        $model_2 = $this->sipBuilder->create();
        $model_3 = $this->sipBuilder->create();
        $model_4 = $this->sipBuilder->create();

        $this->employeeBuilder->setSip($model)->create();
        $this->employeeBuilder->setSip($model_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByHasEmployee('false')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $model_4->id],
                            ['id' => $model_2->id]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByHasEmployee($value): string
    {
        return sprintf(
            '
            {
                %s (has_employee: %s){
                    data {
                        id
                        number
                        employee {
                            id
                        }
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $value
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
