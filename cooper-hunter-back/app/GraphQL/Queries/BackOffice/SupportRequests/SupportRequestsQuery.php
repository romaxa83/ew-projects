<?php


namespace App\GraphQL\Queries\BackOffice\SupportRequests;


use App\GraphQL\Queries\Common\SupportRequests\BaseSupportRequestsQuery;
use GraphQL\Type\Definition\Type;

class SupportRequestsQuery extends BaseSupportRequestsQuery
{

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'technician_email' => [
                    'type' => Type::string(),
                ],
                'technician_name' => [
                    'type' => Type::string(),
                ],
            ]
        );
    }

    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
