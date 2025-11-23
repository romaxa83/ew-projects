<?php

namespace Tests\Feature\Reports\SalesFunel;

use App\Http\Controllers\Reports\SalesFunelReportController;
use App\Models\Division;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Orders\StatusGroupBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class StatusesTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected StatusGroupBuilder $statusGroupBuilder;
    protected StatusBuilder $statusBuilder;


    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->statusGroupBuilder = resolve(StatusGroupBuilder::class);
        $this->statusBuilder = resolve(StatusBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_statuses()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(3)
            ->create();
        $status_group_2 = $this->statusGroupBuilder
            ->in_funel_report(0)
            ->sort(1)
            ->create();
        $status_group_3 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(2)
            ->create();
        $status_group_4 = $this->statusGroupBuilder
            ->in_funel_report(0)
            ->sort(4)
            ->create();
        $status_group_5 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(5)
            ->create();

        $status_1_1 = $this->statusBuilder->group($status_group_1)->create();
        $status_1_2 = $this->statusBuilder->group($status_group_1)->create();

        $status_2_1 = $this->statusBuilder->group($status_group_2)->create();
        $status_2_2 = $this->statusBuilder->group($status_group_2)->create();

        $status_3_1 = $this->statusBuilder->group($status_group_3)->create();
        $status_3_2 = $this->statusBuilder->group($status_group_3)->create();

        $status_4_1 = $this->statusBuilder->group($status_group_4)->create();
        $status_4_2 = $this->statusBuilder->group($status_group_4)->create();

        $status_5_1 = $this->statusBuilder->group($status_group_5)->create();
        $status_5_2 = $this->statusBuilder->group($status_group_5)->create();

        $controller = resolve(SalesFunelReportController::class);

        $result = $controller->getStatuses();

        $this->assertEquals(
            $result, [
                $status_group_3->title => [
                    $status_3_1->id => $status_3_1->title,
                    $status_3_2->id => $status_3_2->title,
                ],
                $status_group_1->title => [
                    $status_1_1->id => $status_1_1->title,
                    $status_1_2->id => $status_1_2->title,
                ],
                $status_group_5->title => [
                    $status_5_1->id => $status_5_1->title,
                    $status_5_2->id => $status_5_2->title,
                ]
            ]
        );
    }

    /** @test */
    public function empty_statuses_in_group()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(3)
            ->create();
        $status_group_2 = $this->statusGroupBuilder
            ->in_funel_report(0)
            ->sort(1)
            ->create();
        $status_group_3 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(2)
            ->create();
        $status_group_4 = $this->statusGroupBuilder
            ->in_funel_report(0)
            ->sort(4)
            ->create();
        $status_group_5 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(5)
            ->create();

        $controller = resolve(SalesFunelReportController::class);

        $result = $controller->getStatuses();

        $this->assertEmpty($result);
    }

    /** @test */
    public function empty_data()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $controller = resolve(SalesFunelReportController::class);

        $result = $controller->getStatuses();

        $this->assertEmpty($result);
    }
}
