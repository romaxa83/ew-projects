<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectUnitsQuery;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Builders\Commercial\ProjectUnitBuilder;
use Tests\TestCase;

class CommercialProjectUnitsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialProjectUnitsQuery::NAME;

    protected $projectBuilder;
    protected $projectUnitBuilder;
    protected $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->projectUnitBuilder = resolve(ProjectUnitBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsTechnicianWithRole();

        $project_1 = $this->projectBuilder->create();
        $project_2 = $this->projectBuilder->create();

        $unit_1 = $this->projectUnitBuilder->setProject($project_1)->create();
        $unit_2 = $this->projectUnitBuilder->setProject($project_1)->create();
        $unit_3 = $this->projectUnitBuilder->setProject($project_1)->create();
        $this->projectUnitBuilder->setProject($project_2)->create();
        $this->projectUnitBuilder->setProject($project_2)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($project_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $unit_3->id],
                            ['id' => $unit_2->id],
                            ['id' => $unit_1->id],
                        ],
                        'meta' => [
                            'total' => 3,
                            'per_page' => 15,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 3,
                            'last_page' => 1,
                            'has_more_pages' => false,
                        ],
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStr($commercialProjectId): string
    {
        return sprintf(
            '
            {
                %s (commercial_project_id: %s){
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
            self::MUTATION,
            $commercialProjectId
        );
    }

    /** @test */
    public function success_by_id(): void
    {
        $this->loginAsTechnicianWithRole();

        $project_1 = $this->projectBuilder->create();
        $project_2 = $this->projectBuilder->create();

        $product_1 = $this->productBuilder->create();

        $unit_1 = $this->projectUnitBuilder->setProject($project_1)->setProduct($product_1)->create();
        $this->projectUnitBuilder->setProject($project_1)->create();
        $this->projectUnitBuilder->setProject($project_1)->create();
        $this->projectUnitBuilder->setProject($project_2)->create();
        $this->projectUnitBuilder->setProject($project_2)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrById($unit_1->id, $project_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            [
                                'id' => $unit_1->id,
                                'product' => [
                                    'id' => $product_1->id
                                ],
                                'serial_number' => $unit_1->serial_number,
                            ],
                        ],
                        'meta' => [
                            'total' => 1,
                        ],
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrById($id, $commercialProjectId): string
    {
        return sprintf(
            '
            {
                %s (id: %s, commercial_project_id: %s){
                       data {
                            id
                            product {
                                id
                            }
                            serial_number
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $id,
            $commercialProjectId
        );
    }

    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $technician = $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $project_1 = $this->projectBuilder->setTechnician($technician)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($project_1->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}
