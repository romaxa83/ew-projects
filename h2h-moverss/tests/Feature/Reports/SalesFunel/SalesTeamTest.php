<?php

namespace Feature\Reports\SalesFunel;

use App\Enums\Employee\SalesTeamEnum;
use App\Models\Division;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class SalesTeamTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;


    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_sales_team()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $this->get(route('reports.sales.funel.report.data.filter.sale-team'))
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'id' => 'all',
                        'title' => 'All',
                    ],
                    [
                        'id' => SalesTeamEnum::Local(),
                        'title' => SalesTeamEnum::Local->label(),
                    ],
                    [
                        'id' => SalesTeamEnum::Local_long(),
                        'title' => SalesTeamEnum::Local_long->label(),
                    ],
                    [
                        'id' => 'na',
                        'title' => 'N/A',
                    ]
                ]
            ])
        ;
    }
}
