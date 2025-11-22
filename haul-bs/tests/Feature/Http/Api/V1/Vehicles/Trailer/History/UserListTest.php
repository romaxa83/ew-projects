<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Trailer\History;

use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\History\HistoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use DatabaseTransactions;

    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;
    protected HistoryBuilder $historyBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $user_1 = $this->userBuilder->firstName('Root')->create();
        $user_2 = $this->userBuilder->firstName('Alen')->create();

        $h_1 = $this->historyBuilder->model($model)->user($user_1)->create();
        $h_2 = $this->historyBuilder->model($model)->user($user_2)->create();
        $this->historyBuilder->create();

        $this->getJson(route('api.v1.vehicles.trailers.history-users-list', ['id' => $model->id]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'phone',
                        'phone_extension',
                        'email',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $user_2->id],
                    ['id' => $user_1->id],
                ]
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

        $this->historyBuilder->create();

        $this->getJson(route('api.v1.vehicles.trailers.history-users-list', ['id' => $model->id]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.vehicles.trailers.history-users-list', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.vehicles.trailer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->getJson(route('api.v1.vehicles.trailers.history-users-list', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->getJson(route('api.v1.vehicles.trailers.history-users-list', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
