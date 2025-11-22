<?php

namespace App\GraphQL\Mutations;

use App\GraphQL\Types\NonNullType;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseActionMutation extends BaseMutation
{
    public function args(): array
    {
        return [
            'ids' => NonNullType::listOf(NonNullType::id()),
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    protected function rules(array $args = []): array
    {
        return [
            'ids' => ['required', 'array', $this->getAdditionalRule($args)],
            'ids.*' => ['required', 'integer'],
        ];
    }

    protected function getAdditionalRule(array $args): mixed
    {
        return null;
    }

    abstract protected function getEntities(array $ids): Collection;

    abstract protected function action(iterable $entities): void;

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $ids = $args['ids'];
        $entities = $this->getEntities($ids);

        $this->action($entities);

        if ($entities->isEmpty()) {
            throw new TranslatedException(trans('messages.action.fail'));
        }

        if ($entities->count() !== count($ids)) {
            throw new TranslatedException(trans('messages.action.warning'));
        }

        return true;
    }
}
