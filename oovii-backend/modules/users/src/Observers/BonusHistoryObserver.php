<?php

namespace WezomCms\Users\Observers;


use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

class BonusHistoryObserver
{
    public function created(BonusHistory $bonusHistory): void
    {
        $this->updateUserBonusSum($bonusHistory->inviter);
    }

    public function deleted(BonusHistory $bonusHistory): void
    {
        $this->updateUserBonusSum($bonusHistory->inviter);
    }

    private function updateUserBonusSum(User $user): void
    {
        $user->loadMissing('inviterBonusHistory');

        $user->updateBonusSum();
    }
}
