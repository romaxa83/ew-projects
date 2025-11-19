<?php

namespace Wezom\Core\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Spatie\LaravelData\Data;
use Wezom\Core\Dto\FilteringDto;

readonly class Context
{
    public function __construct(
        private mixed $root,
        private array $args,
        private ?GraphQLContext $graphQlContext,
        private ?ResolveInfo $resolveInfo
    ) {
    }

    public function getRoot(): mixed
    {
        return $this->root;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getArg(array|int|string $key, mixed $default = null): mixed
    {
        return data_get($this->args, $key, $default);
    }

    /**
     * @template T of Data
     *
     * @param  class-string<T>  $dtoClass
     * @return T|mixed
     */
    public function getDto(string $dtoClass, array|int|string $key, mixed $default = null): mixed
    {
        $defaultPlaceholder = '__DEFAULT__';
        $data = data_get($this->args, $key, $defaultPlaceholder);
        if ($data === $defaultPlaceholder) {
            return $default;
        }

        return $dtoClass::from($data);
    }

    public function getGraphQlContext(): ?GraphQLContext
    {
        return $this->graphQlContext;
    }

    public function getResolveInfo(): ?ResolveInfo
    {
        return $this->resolveInfo;
    }

    public function getFilteringDto(): FilteringDto
    {
        return new FilteringDto(
            $this->getArg('filtering', []),
            $this->getArg('ordering', [])
        );
    }

    public function getUser(): ?Authenticatable
    {
        return $this->graphQlContext?->user();
    }
}
