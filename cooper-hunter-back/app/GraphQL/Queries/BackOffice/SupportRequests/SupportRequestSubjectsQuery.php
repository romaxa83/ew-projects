<?php


namespace App\GraphQL\Queries\BackOffice\SupportRequests;


use App\GraphQL\Queries\Common\SupportRequests\BaseSupportRequestSubjectsQuery;
use GraphQL\Type\Definition\Type;

class SupportRequestSubjectsQuery extends BaseSupportRequestSubjectsQuery
{

    /**
     * @return array
     */
    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'published' => [
                    'type' => Type::boolean(),
                    'description' => 'Get only active/not active items',
                    'rules' => [
                        'nullable',
                        'boolean'
                    ]
                ]
            ]
        );
    }

    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
