<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectProtocolQuery;
use App\Models\Commercial\CommercialProject;
use App\Models\Technicians\Technician;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class CommercialProjectProtocolQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CommercialProjectProtocolQuery::NAME;

    protected $projectBuilder;
    protected $protocolBuilder;
    protected $protocolProjectBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
        $this->protocolProjectBuilder = resolve(ProjectProtocolBuilder::class);
    }

    /** @test */
    public function success_get_one(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();
        $projectProtocol_2 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();
        $projectProtocol_3 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStr($projectProtocol_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $projectProtocol_1->id
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                    id
                }
            }',
            self::QUERY,
            $id
        );
    }


    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($projectProtocol_1->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}


