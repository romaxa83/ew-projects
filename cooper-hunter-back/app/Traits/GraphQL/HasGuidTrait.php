<?php

namespace App\Traits\GraphQL;

use App\Models\Admins\Admin;
use Core\Traits\Auth\AuthGuardsTrait;
use GraphQL\Type\Definition\Type;

trait HasGuidTrait
{
    use AuthGuardsTrait;

    protected function getGuidField(): array
    {
        return [
            'guid' => [
                /**
                 * @see resolveGuidField()
                 */
                'type' => Type::string(),
                'is_relation' => false,
                'selectable' => true,
            ],
        ];
    }

    protected function resolveGuidField($hasGuid): ?string
    {
        $user = $this->getAuthUser();

        if (!$user instanceof Admin) {
            return null;
        }

        return $hasGuid->guid ?? null;
    }
}
