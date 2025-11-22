<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Trailer\History;

use App\Models\Vehicles\Trailer;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\History\HistoryBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;

    protected TrailerBuilder $trailerBuilder;
    protected CustomerBuilder $customerBuilder;
    protected HistoryBuilder $historyBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $user = $this->loginUserAsSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $msg = 'history.vehicle.created';
        $msg_attr = [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'vehicle_type' => 'Trailer',
            'user_id' => $user->id,
        ];

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $h_1 = $this->historyBuilder->model($model)
            ->msg($msg)
            ->msg_attr($msg_attr)
            ->performed_at($date->subHours(2))
            ->create();
        $h_2 = $this->historyBuilder->model($model)->performed_at($date->subHours(1))->create();

        $this->getJson(route('api.v1.vehicles.trailers.list-history', ['id' => $model->id]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'model_id',
                        'user_id',
                        'message',
                        'meta',
                        'performed_at',
                        'performed_timezone',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $h_2->id,],
                    [
                        'id' => $h_1->id,
                        'message' => __($msg, $msg_attr)
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $this->getJson(route('api.v1.vehicles.trailers.list-history', ['id' => $model->id]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.vehicles.trailers.list-history', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.vehicles.trailer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->getJson(route('api.v1.vehicles.trailers.list-history', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->getJson(route('api.v1.vehicles.trailers.list-history', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
