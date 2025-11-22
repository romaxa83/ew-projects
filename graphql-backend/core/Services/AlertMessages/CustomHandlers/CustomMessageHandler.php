<?php

namespace Core\Services\AlertMessages\CustomHandlers;

use App\Entities\Messages\AlertMessageEntity;
use App\Models\Users\User;

interface CustomMessageHandler
{
    public function handle(User $user): ?AlertMessageEntity;
}
