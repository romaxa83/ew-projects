<?php

namespace App\GraphQL\Mutations\BackOffice\Chat;

use Core\Chat\GraphQL\Mutations\BaseReadMessagesMutation;

class ReadMessagesMutation extends BaseReadMessagesMutation
{
    public function __construct()
    {
        $this->setAdminGuard();
    }
}
