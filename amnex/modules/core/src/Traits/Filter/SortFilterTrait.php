<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Filter;

use BackedEnum;
use BenSampo\Enum\Enum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * @property Builder $query
 */
trait SortFilterTrait
{
    /**
     * @see SortFilterTrait::getTranslationTable()
     * @var array<string>
     */
    protected array $translationOrderingFields = [];

    private array $joinedSortingRelations = [];

    public function ordering(array $sortOptions): void
    {
        foreach ($sortOptions as $sortOption) {
            $field = $this->extractEnumValue($sortOption['column']);
            $direction = $this->extractEnumValue($sortOption['direction']);

            $this->executeSorting($field, $direction);
        }

        $primaryKeyName = $this->getPrimaryKeyName();

        $hasSortByPrimaryKey = collect($sortOptions)
            ->map(fn (array $value) => $this->extractEnumValue($value['column']))
            ->contains($primaryKeyName);

        if (!$hasSortByPrimaryKey) {
            $this->executeSorting($this->getFullPrimaryKeyName(), 'desc');
        }
    }

    private function extractEnumValue(Enum|BackedEnum|UnitEnum $column): string
    {
        return match (true) {
            $column instanceof Enum, $column instanceof BackedEnum => $column->value,
            $column instanceof UnitEnum => $column->name,
        };
    }

    protected function executeSorting(string $field, string $direction): void
    {
        if ($this->isTranslationField($field)) {
            $this->orderByTranslationColumn($field, $direction);
        } elseif ($this->existCustomSortMethod($field)) {
            $this->executeCustomSortMethod($field, $direction);
        } else {
            $this->orderQuery($field, $direction);
        }
    }

    protected function getQueryBuilder(): Builder
    {
        return $this->query;
    }

    private function existCustomSortMethod(string $field): bool
    {
        return method_exists($this, $this->getCustomSortMethodName($field));
    }

    private function executeCustomSortMethod(string $field, string $direction): void
    {
        $this->{$this->getCustomSortMethodName($field)}($direction);
    }

    private function getCustomSortMethodName(string $field): string
    {
        return 'custom' . Str::studly($field) . 'Sort';
    }

    protected function orderQuery(string $field, string $direction): void
    {
        $this->orderBy($field, $direction);
    }

    private function isTranslationField(string $field): bool
    {
        return in_array($field, $this->translationOrderingFields);
    }

    private function orderByTranslationColumn(string $field, string $direction): void
    {
        $relation = $this->getTranslationTable();
        $relationAlias = "{$relation}_sorting";

        if (!$this->hasJoined($relationAlias)) {
            $this->joinTranslationRelation($relation, $relationAlias);
        }

        $this->getQueryBuilder()->orderBy("$relationAlias.$field", $direction);
    }

    private function hasJoined(string $table): bool
    {
        return in_array($table, $this->joinedSortingRelations);
    }

    private function joinTranslationRelation(string $relation, string $relationAlias): void
    {
        $this
            ->getQueryBuilder()
            ->leftJoin(
                "$relation as $relationAlias",
                fn (JoinClause $j) => $j
                    ->on($this->getFullPrimaryKeyName(), "$relationAlias.row_id")
                    ->where("$relationAlias.language", Lang::getLocale())
            );

        $this->getQueryBuilder()->select("{$this->getTable()}.*");

        $this->joinedSortingRelations[] = $relationAlias;
    }

    private function getPrimaryKeyName(): string
    {
        return $this->getQueryBuilder()->getModel()->getKeyName();
    }

    private function getFullPrimaryKeyName(): string
    {
        return $this->getTable() . '.' . $this->getPrimaryKeyName();
    }

    private function getTable(): string
    {
        return $this->getQueryBuilder()->getModel()->getTable();
    }

    protected function getTranslationTable(): string
    {
        return Str::singular($this->getTable()) . '_translations';
    }
}
