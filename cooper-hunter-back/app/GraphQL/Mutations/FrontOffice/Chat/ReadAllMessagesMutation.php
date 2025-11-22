<?php

namespace App\GraphQL\Mutations\FrontOffice\Chat;

use Core\Chat\GraphQL\Mutations\BaseReadAllMessagesMutation;

class ReadAllMessagesMutation extends BaseReadAllMessagesMutation
{
    public function __construct()
    {
        $this->setTechnicianGuard();
    }
}
