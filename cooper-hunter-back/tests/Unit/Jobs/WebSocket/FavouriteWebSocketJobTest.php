<?php

namespace Unit\Jobs\WebSocket;

use App\Enums\Favourites\FavouriteSubscriptionActionEnum;
use App\Models\Catalog\Favourites\Favourite;
use App\Models\Catalog\Products\Product;
use App\Services\Favourites\FavouriteService;
use Core\WebSocket\Jobs\WsBroadcastJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class FavouriteWebSocketJobTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_job_by_create_favourite(): void
    {
        Bus::fake();

        $favourite = Favourite::factory()
            ->create();

        Bus::assertDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData($job, $favourite)
        );
    }

    private function checkJobData(
        WsBroadcastJob $job,
        Favourite $favourite,
        string $action = FavouriteSubscriptionActionEnum::CREATED
    ): bool {
        $member = $favourite->member;
        if ($job->getUser()->id !== $member->getId()) {
            return false;
        }

        if ($job->getUser()
                ->getMorphType() !== $member->getMorphType()) {
            return false;
        }

        $context = $job->getContext();

        if (empty($context) || !Arr::has($context, ['favourite_id', 'favourite_type', 'action'])) {
            return false;
        }

        if ($context['favourite_id'] !== $favourite->favorable_id) {
            return false;
        }

        if ($context['favourite_type'] !== $favourite->favorable_type) {
            return false;
        }

        if ($context['action'] !== $action) {
            return false;
        }

        return true;
    }

    public function test_job_by_delete_favourite(): void
    {
        Bus::fake();

        $favourite = Favourite::factory()
            ->createQuietly();

        $favourite->delete();

        Bus::assertDispatched(
            fn(WsBroadcastJob $job): bool => $this->checkJobData(
                $job,
                $favourite,
                FavouriteSubscriptionActionEnum::DELETED
            )
        );
    }

    public function test_job_by_delete_many_favourite(): void
    {
        Bus::fake();

        $favourites = Favourite::factory()
            ->count(5)
            ->createQuietly();

        $favourite = $favourites[0];

        $service = resolve(FavouriteService::class);

        $service->removeAll($favourite->member, new Product());

        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($favourite): bool
            {
                $member = $favourite->member;
                if ($job->getUser()->id !== $member->getId()) {
                    return false;
                }

                if ($job->getUser()
                        ->getMorphType() !== $member->getMorphType()) {
                    return false;
                }


                $context = $job->getContext();

                if (empty($context) || !Arr::has($context, ['favourite_id', 'favourite_type', 'action'])) {
                    return false;
                }

                if ($context['favourite_id'] !== null) {
                    return false;
                }

                if ($context['favourite_type'] !== $favourite->favorable_type) {
                    return false;
                }

                if ($context['action'] !== FavouriteSubscriptionActionEnum::DELETED_ALL) {
                    return false;
                }

                return true;
            }
        );
    }
}
