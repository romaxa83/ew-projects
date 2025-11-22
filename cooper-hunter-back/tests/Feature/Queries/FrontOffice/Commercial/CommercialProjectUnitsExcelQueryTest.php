<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectUnitsExcelQuery;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Builders\Commercial\ProjectUnitBuilder;
use Tests\TestCase;

class CommercialProjectUnitsExcelQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialProjectUnitsExcelQuery::NAME;

    protected $projectBuilder;
    protected $projectUnitBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->projectUnitBuilder = resolve(ProjectUnitBuilder::class);
    }

    /** @test */
    public function success_get_link(): void
    {
        $this->loginAsTechnicianWithRole();

        $project_1 = $this->projectBuilder->create();

        $this->projectUnitBuilder->setProject($project_1)->create();
        $this->projectUnitBuilder->setProject($project_1)->create();
        $this->projectUnitBuilder->setProject($project_1)->create();

        $fileName = "units-{$project_1->id}.xlsx";

        $this->postGraphQL([
            'query' => $this->getQueryStr($project_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' =>  url("/storage/exports/commercial-project/{$fileName}"),
                        'type' => 'success'
                    ],
                ]
            ])
        ;

        $storagePath = storage_path("app/public/exports/commercial-project/{$fileName}");

        $this->assertTrue(file_exists($storagePath));

        unlink($storagePath);
    }

    /** @test */
    public function fail_not_commercial_project(): void
    {
        $this->loginAsTechnicianWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr(1)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => "Model not found",
                        'type' => 'warning'
                    ],
                ]
            ])
        ;
    }

    protected function getQueryStr($commercialProjectId): string
    {
        return sprintf(
            '
            {
                %s (commercial_project_id: %s){
                    message
                    type
                }
            }',
            self::MUTATION,
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
