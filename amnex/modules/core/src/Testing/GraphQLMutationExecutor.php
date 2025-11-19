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

class GraphQLMutationExecutor
{
    private bool $paginateMode = false;
    private GraphQLQuery $mutation;
    private array $headers = [];

    final protected function __construct(private readonly string $name, private readonly TestCase $testCase)
    {
        $this->mutation = GraphQLQuery::mutation($name);
    }

    public static function mutation(string $name, TestCase $testCase): static
    {
        return new static($name, $testCase);
    }

    public static function upload(string $name, TestCase $testCase): static
    {
        $instance = (new static($name, $testCase));

        $instance->uploadMode();

        return $instance;
    }

    protected function uploadMode(): void
    {
        $this->mutation->uploadMode();
    }

    public function paginateMode(): static
    {
        $this->paginateMode = true;

        return $this;
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function args(array $args = []): static
    {
        $this->mutation->args($args);

        return $this;
    }

    public function ordering(OrderColumnEnumInterface $column, OrderDirectionEnum $direction): static
    {
        $this->mutation->ordering($column, $direction);

        return $this;
    }

    public function orderingParamName(string $name): static
    {
        $this->mutation->orderingParamName($name);

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
        $this->mutation->select($select);

        return $this;
    }

    public function selectResponseMessage(): static
    {
        $this->mutation->select(ResponseMessageProjection::root()->get());

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

    public function executeOne(): array
    {
        return $this->executeAndReturnResponse()
            ->assertNoErrors()
            ->json('data.' . $this->name);
    }

    public function executeAndReturnResponse(): TestResponse
    {
        if ($this->mutation->isUploadMode()) {
            return $this->testCase->postGraphQlUpload($this->mutation->make());
        }

        return $this->testCase->postGraphQL($this->mutation->make(), $this->headers);
    }
}
