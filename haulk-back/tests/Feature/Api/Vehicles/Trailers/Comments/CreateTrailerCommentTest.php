<?php

namespace Tests\Feature\Api\Vehicles\Trailers\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\TrailerComment;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\Comments\CreateCommentTest;

class CreateTrailerCommentTest extends CreateCommentTest
{
    protected string $routeName = 'trailers.comments.store';

    protected string $tableName = TrailerComment::TABLE_NAME;
    protected string $relatedColumnName = 'trailer_id';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Trailer::class)->create();
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsBodyShopAdmin();
    }
}
