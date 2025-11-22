<?php

namespace Tests\Unit\Services\AA\Commands;

use App\Models\AA\AAPost;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Services\AA\Commands\CreateOrder;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\Builders\AAPostBuilder;
use Tests\Traits\Builders\RecommendationBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class CreateOrderTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use OrderBuilder;
    use RecommendationBuilder;
    use AAPostBuilder;

    /** @test*/
    public function assert_generate_data(): void
    {
        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->setAlias('arma-motors-renault')->create();

        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $user = $this->userBuilder()->setUuid($userUuid)->create();

        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $car = $this->carBuilder()->setUserId($user->id)->setUuid($carUuid)->create();

        $recommendation = $this->recommendationBuilder()->create();

        $service = Service::find(1);
        $service->update(['time_step' => 60]);

        $dealership = Dealership::find(1);

        $onDate = CarbonImmutable::now();

        $order = $this->orderBuilder()
            ->setUserId($user->id)
            ->setCarId($car->id)
            ->setServiceId($service->id)
            ->setDealershipId($dealership->id)
            ->setRecommendationId($recommendation->id)
            ->setOnDate($onDate)
            ->setPostUuid($post->uuid)
            ->asOne()
            ->create();

        $order->refresh();

        $data = resolve(CreateOrder::class)->generateData($order);

        $this->assertEmpty(Arr::get($data, 'data.id'));
        $this->assertEquals(Arr::get($data, 'data.client'), $userUuid);
        $this->assertEquals(Arr::get($data, 'data.auto'), $carUuid);
        $this->assertEquals(Arr::get($data, 'data.type'), $service->alias);
        $this->assertEquals(Arr::get($data, 'data.comment'), $order->communication);
        $this->assertEquals(Arr::get($data, 'data.idRecommendation'), $recommendation->uuid);
        $this->assertEquals(Arr::get($data, 'data.workshop'), $post->uuid);
        $this->assertEquals(Arr::get($data, 'data.startDate'), $onDate->timestamp);
        $this->assertEquals(Arr::get($data, 'data.endDate'), $onDate->addMinutes($service->time_step)->timestamp);
        $this->assertEquals(Arr::get($data, 'data.planning.0.endDate'), $onDate->addMinutes($service->time_step)->timestamp);
        $this->assertEquals(Arr::get($data, 'data.planning.0.startDate'), $onDate->timestamp);
        $this->assertEquals(Arr::get($data, 'data.planning.0.workshop'), $post->uuid);
    }

    /** @test*/
    public function without_aa_post(): void
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $user = $this->userBuilder()->setUuid($userUuid)->create();

        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $car = $this->carBuilder()->setUserId($user->id)->setUuid($carUuid)->create();

        $recommendation = $this->recommendationBuilder()->create();

        $service = Service::find(1);
        $dealership = Dealership::find(1);

        $onDate = CarbonImmutable::now();

        $order = $this->orderBuilder()
            ->setUserId($user->id)
            ->setCarId($car->id)
            ->setServiceId($service->id)
            ->setDealershipId($dealership->id)
            ->setRecommendationId($recommendation->id)
            ->setOnDate($onDate)
            ->asOne()
            ->create();

        $order->refresh();

        $data = resolve(CreateOrder::class)->generateData($order);

        $this->assertNull(Arr::get($data, 'data.workshop'));
        $this->assertNull(Arr::get($data, 'data.planning.0.workshop'));
    }
}

