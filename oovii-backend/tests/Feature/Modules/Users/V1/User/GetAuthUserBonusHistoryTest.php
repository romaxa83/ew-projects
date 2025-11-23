<?php

namespace Tests\Feature\Modules\Users\V1\User;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Orders\Models\Order;
use WezomCms\Users\Enums\BonusHistoryType;
use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

class GetAuthUserBonusHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    public function test_it_returns_user_bonus_history(): void
    {
        $inviter = $this->loginAsUser();
        $inviter->bonus = 0;
        $inviter->save();

        $this->createBonusHistory($inviter);
        $inviter->refresh();

        $res = $this->getJson(route('api.v1.mobile.user.bonus-history'))
            ->assertOk()
            ->assertJson($this->structureResource([
                'id' => $inviter->id,
                'total_plus' => 400,
                'total_minus' => 220,
                'bonus' => 180,
            ]));

        $history = $res->json('data.bonus_history');

        self::assertCount(6, $history);
    }

    public function test_not_auth(): void
    {
        $this->get(route('api.v1.mobile.user.bonus-history'), $this->headers())
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse(__("cms-core::site.Unauthenticated")));
    }

    private function createBonusHistory(User $inviter): void
    {
        // accrual history
        BonusHistory::factory()
            ->count(2)
            ->state(new Sequence(
                [ 'bonus' => 100, 'created_at' => Carbon::now()->subDays(2) ],
                [ 'bonus' => 200, 'created_at' => Carbon::now()->subDays(12) ]
            ))
            ->create([
                'inviter_id' => $inviter->id,
            ])
            ->each(function (BonusHistory $bonusHistory) {
                /** @var Order $order */
                $order = Order::factory()->create();

                $bonusHistory->order_id = $order->id;
                $bonusHistory->referral_id = $order->user->id;
                $bonusHistory->save();
            });

        // use history
        BonusHistory::factory()
            ->count(2)
            ->state(new Sequence(
                [ 'bonus' => 50, 'created_at' => Carbon::now()->subDay() ],
                [ 'bonus' => 120, 'created_at' => Carbon::now()->subDays(11) ]
            ))
            ->create([
                'inviter_id' => $inviter->id,
                'type' => BonusHistoryType::USE,
            ])
            ->each(function (BonusHistory $bonusHistory) use ($inviter) {
                /** @var Order $order */
                $order = Order::factory()->create([ 'user_id' => $inviter->id ]);

                $bonusHistory->order_id = $order->id;
                $bonusHistory->save();
            });

        // adjustment history
        BonusHistory::factory()
            ->count(2)
            ->state(new Sequence(
                [ 'bonus' => 100, 'created_at' => Carbon::now()->subDays(3), 'type' => BonusHistoryType::ADJUSTMENT_PLUS() ],
                [ 'bonus' => 50, 'created_at' => Carbon::now()->subDays(9), 'type' => BonusHistoryType::ADJUSTMENT_MINUS() ]
            ))
            ->create([
                'inviter_id' => $inviter->id,
            ]);

        $inviter->updateBonusSum();
    }
}
