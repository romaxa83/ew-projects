<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Users\Enums\BonusHistoryType;
use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

class ReferralSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(5)->create();

        $users->each(function (User $user) {
            User::factory(3)
                ->create([ 'ref_id' => $user->id ])
                ->each(function (User $referral) use ($user) {
                    BonusHistory::factory(5)
                        ->create([
                            'type' => BonusHistoryType::ACCRUAL,
                            'inviter_id' => $user->id,
                            'referral_id' => $referral->id,
                        ]);
                });
        });
    }
}

