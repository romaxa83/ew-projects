<?php

namespace Core\Chat\GraphQL\Queries;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Exceptions\MessageableException;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Error\AuthorizationError;

abstract class BaseChatQuery extends BaseQuery
{
    public function args(): array
    {
        return [
            'created_at' => [
                'name' => 'created_at',
                'type' => Type::string()
            ],
            'updated_at' => [
                'name' => 'updated_at',
                'type' => Type::string()
            ],
            'per_page' => [
                'type' => Type::int(),
                'defaultValue' => config('queries.default.pagination.per_page')
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ],
        ];
    }

    /**
     * @throws AuthorizationError
     * @throws MessageableException
     */
    protected function getUser(): Messageable
    {
        if (is_null($user = $this->user())) {
            throw new AuthorizationError($this->getAuthorizationMessage());
        }

        if (!$user instanceof Messageable) {
            throw new MessageableException();
        }

        return $user;
    }
}
