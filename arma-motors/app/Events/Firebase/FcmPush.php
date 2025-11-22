<?php

namespace App\Events\Firebase;

use App\Models\User\User;
use App\Services\Firebase\FcmAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class FcmPush
{
    use SerializesModels;

    public function __construct(
        public User $user,
        public FcmAction $action,
        public null|Model $relatedModel = null
    )
    {}
}
