<?php

namespace App\GraphQL\Mutations\FrontOffice\Chat;

use Core\Chat\GraphQL\Mutations\BaseReadMessagesMutation;

class ReadMessagesMutation extends BaseReadMessagesMutation
{
    public function __construct()
    {
        $this->setTechnicianGuard();
    }
}
