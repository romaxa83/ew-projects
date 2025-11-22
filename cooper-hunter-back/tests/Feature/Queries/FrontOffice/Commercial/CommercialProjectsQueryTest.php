<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectsQuery;
use App\Models\Commercial\CommercialProject;
use App\Models\Technicians\Technician;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Feature\Queries\Common\Commercial\AbstractCommercialProjectsQueryTest;

class CommercialProjectsQueryTest extends AbstractCommercialProjectsQueryTest
{
    public const QUERY = CommercialProjectsQuery::NAME;

    protected $projectBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
    }

    public function test_get_list(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        CommercialProject::factory()
            ->times(5)
            ->for($technician, 'member')
            ->create();

        $query = $this->getQuery(self::QUERY);

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(5, 'data.' . self::QUERY . '.data');
    }

    /** @test */
    public function filter_by_id(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $project_1 = $this->projectBuilder->setTechnician($technician)->create();
        $this->projectBuilder->setTechnician($technician)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStrFilterByID($project_1->id)
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $project_1->id
                            ]
                        ],
                        'meta' => [
                            'total' => 1
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data');
    }

    /** @test */
    public function filter_by_name(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $project_1 = $this->projectBuilder->setData([
            'name' => 'test 1'
        ])->setTechnician($technician)->create();
        $project_2 = $this->projectBuilder->setData([
            'name' => 'test 23'
        ])->setTechnician($technician)->create();

        $this->projectBuilder->setData([
            'name' => 'tourist'
        ])->setTechnician($technician)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStrFilterByName('tes')
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'meta' => [
                            'total' => 2
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.' . self::QUERY . '.data');
    }

    protected function getQueryStrFilterByName($name): string
    {
        return sprintf(
            '
            {
                %s (name: "%s"){
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $name
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
        $this->projectBuilder->setTechnician($technician)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrFilterByID($project_1->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }

    protected function getQueryStrFilterByID($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s){
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }
}
