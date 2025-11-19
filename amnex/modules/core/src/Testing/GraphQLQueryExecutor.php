<?php

declare(strict_types=1);

namespace Wezom\Core\Testing;

use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Contracts\OrderColumnEnumInterface;
use Wezom\Core\Enums\OrderDirectionEnum;
use Wezom\Core\Testing\Projections\Projection;
use Wezom\Core\Testing\Projections\ResponseMessageProjection;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class GraphQLQueryExecutor
{
    private bool $paginateMode = false;
    private GraphQLQuery $query;
    private array $headers = [];

    final protected function __construct(private readonly string $name, private readonly TestCase $testCase)
    {
        $this->query = GraphQLQuery::query($name);
    }

    public static function query(string $name, TestCase $testCase): static
    {
        return new static($name, $testCase);
    }

    public function paginateMode(): static
    {
        $this->paginateMode = true;

        return $this;
    }

    public function args(array $args = []): static
    {
        $this->query->args($args);

        return $this;
    }

    public function ordering(OrderColumnEnumInterface $column, OrderDirectionEnum $direction): static
    {
        $this->query->ordering($column, $direction);

        return $this;
    }

    public function orderingParamName(string $name): static
    {
        $this->query->orderingParamName($name);

        return $this;
    }

    public function language(string $lang): static
    {
        throw_unless(app('localization')->hasLang($lang));

        $this->headers[config('translations.header')] = $lang;

        return $this;
    }

    public function select(array|string|Projection $select): static
    {
        if ($select instanceof Projection) {
            $select = $select->get();
        } else {
            $select = is_array($select) ? $select : func_get_args();
        }

        if ($this->paginateMode) {
            $select = ['data' => $select];
        }
        $this->query->select($select);

        return $this;
    }

    public function selectResponseMessage(): static
    {
        $this->query->select(ResponseMessageProjection::root()->get());

        return $this;
    }

    public function execute(): Collection
    {
        $result = $this->executeAndReturnResponse()
            ->assertNoErrors()
            ->json('data.' . $this->name);

        if ($this->paginateMode) {
            return collect($result['data']);
        }

        return collect($result);
    }

    public function executeOne(): ?array
    {
        return $this->executeAndReturnResponse()
            ->assertNoErrors()
            ->json('data.' . $this->name);
    }

    public function executeAndReturnResponse(): TestResponse
    {
        return $this->testCase->postGraphQL($this->query->make(), $this->headers);
    }
}
