<?php

namespace Tests\Feature\Queries\Dashboard;

use App\Exceptions\ErrorsCode;
use App\Helpers\Month;
use App\Models\Catalogs\Car\Brand;
use App\Types\Order\Status;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;

class DashboardCountOrdersTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use OrderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);
        $year = CarbonImmutable::now()->year;
        $nowMonth = CarbonImmutable::now();
        $subMonth = CarbonImmutable::now()->subMonth();
        // актуально для января
        if($subMonth->year != $year){
            $subMonth = CarbonImmutable::now()->addMonth();
        }

        $aliasVolvo = 'Volvo';
        $aliasRenault = 'RENAULT';
        $aliasMits = 'Mitsubishi';

        $brandVolvo = Brand::where('name', $aliasVolvo)->first();
        $brandRenault = Brand::where('name', $aliasRenault)->first();
        $brandMits = Brand::where('name', $aliasMits)->first();

        $countNowMonthVolvo = 5;
        $countNowMonthRenault = 6;
        $countNowMonthMits = 7;

        $countSubMonthVolvo = 15;
        $countSubMonthRenault = 16;
        $countSubMonthMits = 17;

        $this->orderBuilder()->setBrandId($brandVolvo->id)->setStatus(Status::CLOSE)
            ->setClosedAt($nowMonth)->setCount($countNowMonthVolvo)->create();
        $this->orderBuilder()->setBrandId($brandRenault->id)->setStatus(Status::CLOSE)
            ->setClosedAt($nowMonth)->setCount($countNowMonthRenault)->create();
        $this->orderBuilder()->setBrandId($brandMits->id)->setStatus(Status::CLOSE)
            ->setClosedAt($nowMonth)->setCount($countNowMonthMits)->create();

        $this->orderBuilder()->setBrandId($brandVolvo->id)->setStatus(Status::CLOSE)
            ->setClosedAt($subMonth)->setCount($countSubMonthVolvo)->create();
        $this->orderBuilder()->setBrandId($brandRenault->id)->setStatus(Status::CLOSE)
            ->setClosedAt($subMonth)->setCount($countSubMonthRenault)->create();
        $this->orderBuilder()->setBrandId($brandMits->id)->setStatus(Status::CLOSE)
            ->setClosedAt($subMonth)->setCount($countSubMonthMits)->create();

        $this->orderBuilder()->setCount(8)->create();

        $response = $this->graphQL($this->getQueryStr($year))
            ->assertOk();

        $responseData = $response->json('data.dashboardCountOrders');
        $this->assertArrayHasKey('month', $responseData[0]);
        $this->assertArrayHasKey('data', $responseData[0]);
        $this->assertArrayHasKey('brand', $responseData[0]['data'][0]);
        $this->assertArrayHasKey('count', $responseData[0]['data'][0]);
//dd($responseData);
        foreach ($responseData as $key => $data){
            $this->assertEquals(Month::monthAsArray()[$key], $data['month']);
            $this->assertEquals(3, count($data['data']));

            $mergeOneArray = mergeOneArray($data['data']);
            $this->assertTrue(in_array($aliasVolvo, $mergeOneArray));
            $this->assertTrue(in_array($aliasRenault, $mergeOneArray));
            $this->assertTrue(in_array($aliasMits, $mergeOneArray));

            foreach ($data['data'] as $item){
                if($item['brand'] == $aliasRenault){
                    if(($key + 1) == $nowMonth->month){
                        $this->assertEquals($item['count'], $countNowMonthRenault);
                    } else if(($key + 1) == $subMonth->month){
                        $this->assertEquals($item['count'], $countSubMonthRenault);
                    } else {
                        $this->assertEquals($item['count'], 0);
                    }
                }
                if($item['brand'] == $aliasVolvo){
                    if(($key + 1) == $nowMonth->month){
                        $this->assertEquals($item['count'], $countNowMonthVolvo);
                    } else if(($key + 1) == $subMonth->month){
                        $this->assertEquals($item['count'], $countSubMonthVolvo);
                    } else {
                        $this->assertEquals($item['count'], 0);
                    }
                }
                if($item['brand'] == $aliasMits){
                    if(($key + 1) == $nowMonth->month){
                        $this->assertEquals($item['count'], $countNowMonthMits);
                    } else if(($key + 1) == $subMonth->month){
                        $this->assertEquals($item['count'], $countSubMonthMits);
                    } else {
                        $this->assertEquals($item['count'], 0);
                    }
                }
            }
        }
    }

    /** @test */
    public function success_only_one_brand_in_month()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);
        $year = CarbonImmutable::now()->year;
        $nowMonth = CarbonImmutable::now();

        $aliasVolvo = 'Volvo';
        $aliasRenault = 'RENAULT';
        $aliasMits = 'Mitsubishi';

        $brandVolvo = Brand::where('name', $aliasVolvo)->first();

        $countNowMonthVolvo = 5;

        $this->orderBuilder()->setBrandId($brandVolvo->id)->setStatus(Status::CLOSE)->setClosedAt($nowMonth)->setCount($countNowMonthVolvo)->create();

        $response = $this->graphQL($this->getQueryStr($year))
            ->assertOk();

        $responseData = $response->json('data.dashboardCountOrders');
        $this->assertArrayHasKey('month', $responseData[0]);
        $this->assertArrayHasKey('data', $responseData[0]);
        $this->assertArrayHasKey('brand', $responseData[0]['data'][0]);
        $this->assertArrayHasKey('count', $responseData[0]['data'][0]);

        foreach ($responseData as $key => $data){
            $this->assertEquals(Month::monthAsArray()[$key], $data['month']);
            $this->assertEquals(3, count($data['data']));

            $mergeOneArray =  mergeOneArray($data['data']);
            $this->assertTrue(in_array($aliasVolvo, $mergeOneArray));
            $this->assertTrue(in_array($aliasRenault, $mergeOneArray));
            $this->assertTrue(in_array($aliasMits, $mergeOneArray));

            foreach ($data['data'] as $item){
                if($item['brand'] == $aliasVolvo){
                    if(($key + 1) == $nowMonth->month){
                        $this->assertEquals($item['count'], $countNowMonthVolvo);
                    }  else {
                        $this->assertEquals($item['count'], 0);
                    }
                }

                if($item['brand'] == $aliasMits){
                    $this->assertEquals($item['count'], 0);
                }
                if($item['brand'] == $aliasRenault){
                    $this->assertEquals($item['count'], 0);
                }
            }
        }
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->setName(config('permission.roles.super_admin'))->create();
        $year = CarbonImmutable::now()->year;

        $response = $this->graphQL($this->getQueryStr($year));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(int $year): string
    {
        return  sprintf('{
            dashboardCountOrders (
                year: %d
            ) {
                 month
                 data {
                    brand
                    count
                 }
               }
            }',
        $year
        );
    }
}





