<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\QueryBuilder;

use BackedEnum;
use BenSampo\Enum\Enum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Stringable;
use JsonException;
use Symfony\Component\VarDumper\VarDumper;
use UnitEnum;
use Wezom\Core\Contracts\OrderColumnEnumInterface;
use Wezom\Core\Enums\OrderDirectionEnum;
use Wezom\Core\ValueObjects\AbstractValueObject;

class GraphQLQuery
{
    protected const array QUERY_TYPES
        = [
            'query' => 'query',
            'mutation' => 'mutation',
            'upload' => 'upload',
        ];
    protected const string ORDERING_PARAM_NAME = 'ordering';

    protected string $queryType = self::QUERY_TYPES['query'];
    protected array $args;
    protected array $ordering;
    protected string $orderingParamName = self::ORDERING_PARAM_NAME;
    protected array $select;
    protected string $name;
    protected string $query;
    protected bool $hasFiles = false;
    protected array $map = [];
    protected array|UploadedFile|null $files = [];
    protected array $fileVariableNames;

    protected function __construct()
    {
    }

    public static function query(string $name): self
    {
        $self = new self();
        $self->name($name);

        $self->queryType = self::QUERY_TYPES['query'];

        return $self;
    }

    protected function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public static function mutation(string $name): self
    {
        $self = new self();
        $self->name($name);

        $self->queryType = self::QUERY_TYPES['mutation'];

        return $self;
    }

    public static function upload(string $name): self
    {
        $self = new self();
        $self->name($name);

        $self->uploadMode();

        return $self;
    }

    public function args(array $args = []): self
    {
        $this->args = $args;

        return $this;
    }

    public function ordering(OrderColumnEnumInterface $column, OrderDirectionEnum $direction): self
    {
        $this->ordering[] = compact('column', 'direction');

        return $this;
    }

    public function orderingParamName(string $name): self
    {
        $this->orderingParamName = $name;

        return $this;
    }

    public function select(array $select = []): self
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function make(): array
    {
        $this->buildQuery();

        return match ($this->queryType) {
            self::QUERY_TYPES['mutation'] => $this->getMutation(),
            self::QUERY_TYPES['upload'] => $this->getUploadMutation(),
            default => $this->getQuery(),
        };
    }

    protected function buildQuery(): void
    {
        if (!empty($this->query)) {
            return;
        }

        $this->query = '{' . $this->name . $this->convertToGQLArgs() . $this->convertToGQLSelect() . '}';
    }

    protected function convertToGQLArgs(): string
    {
        if (!$this->hasArgs() && !$this->hasOrdering()) {
            return '';
        }

        $argsString = '(';

        if ($this->hasArgs()) {
            $argsString .= $this->buildArgsString();
        }

        if ($this->hasOrdering()) {
            $argsString .= $this->buildOrderingString();
        }

        $argsString = trim($argsString, ', ');

        $argsString .= ')';

        return $argsString;
    }

    private function hasArgs(): bool
    {
        return !empty($this->args);
    }

    private function hasOrdering(): bool
    {
        return !empty($this->ordering);
    }

    private function buildArgsString(): string
    {
        $result = '';
        foreach ($this->args as $key => $value) {
            $result .= $this->resolveValue($key, $value);
        }

        return $result;
    }

    private function buildOrderingString(): string
    {
        return $this->resolveValue($this->orderingParamName, $this->ordering);
    }

    protected function resolveValue(int|string $key, mixed $value, ?string $path = null): string
    {
        if ($this->isScalar($value)) {
            return $this->resolveScalarValue($key, $value);
        }

        if ($value instanceof UploadedFile || (is_array($value) && $this->arrayIsListOfFiles($value))) {
            return $this->resolveFiles($key, $value, $path);
        }

        $path = implode('.', array_filter([$path, $key], static fn ($v) => !is_null($v)));

        $object = $this->resolveArrayValue($value, $path);

        if (is_string($key) && array_is_list($value)) {
            return $key . ': [' . $this->normalizeObject($object) . '], ';
        }

        if (is_string($key)) {
            return $key . ': ' . $object;
        }

        return $object;
    }

    protected function isScalar(mixed $value): bool
    {
        return is_scalar($value)
            || is_null($value)
            || $value instanceof Stringable
            || $value instanceof Enum
            || $value instanceof UnitEnum
            || $value instanceof AbstractValueObject;
    }

    protected function resolveScalarValue(int|string $key, mixed $value): string
    {
        return sprintf(
            '%s: %s, ',
            $key,
            match (true) {
                $value instanceof BackedEnum => $value->name,
                $value instanceof UnitEnum => $value->name,
                $value instanceof Enum => $value->key,
                $value instanceof Stringable => (string)$value,
                is_int($value), is_float($value) => $value,
                is_bool($value) => $value ? 'true' : 'false',
                is_null($value) => 'null',
                default => '"' . $value . '"',
            }
        );
    }

    protected function arrayIsListOfFiles(array $value): bool
    {
        if (empty($value)) {
            return false;
        }

        foreach ($value as $k => $v) {
            if (!is_int($k)) {
                return false;
            }

            if (!$v instanceof UploadedFile) {
                return false;
            }
        }

        return true;
    }

    protected function resolveFiles(
        string $key,
        array|UploadedFile $value,
        ?string $path = null
    ): string {
        $this->hasFiles = true;

        $variableKey = str(collect([$path, $key])->filter()->implode('_'))->replace('.', '_')->camel()->value();
        $fileVariableKey = $variableKey;
        $fileVariableName = '$' . $fileVariableKey;

        $this->map[] = sprintf('"%s": ["variables.%s"]', $variableKey, $variableKey);
        if (is_array($value)) {
            $this->fileVariableNames[$fileVariableName] = [$variableKey];
        } else {
            $this->fileVariableNames[$fileVariableName] = $variableKey;
        }

        $this->files[$fileVariableKey] = $value;

        if (is_array($value)) {
            $fileVariable = "$fileVariableName";
        } else {
            $fileVariable = $fileVariableName;
        }

        return sprintf('%s: %s, ', $key, $fileVariable);
    }

    protected function resolveArrayValue(array $value, ?string $path = null): string
    {
        $object = '';
        if ($this->arrayIsListOfScalars($value)) {
            return $this->implodeScalars($value);
        }

        foreach ($value as $k => $v) {
            $object .= $this->resolveValue($k, $v, $path);
        }

        $object = trim($object, ', ');

        return '{' . $object . '}, ';
    }

    protected function arrayIsListOfScalars(array $value): bool
    {
        foreach ($value as $k => $v) {
            if (!is_int($k)) {
                return false;
            }
            if (!is_scalar($v) && !$v instanceof Stringable && !$v instanceof Enum && !$v instanceof UnitEnum) {
                return false;
            }
        }

        return true;
    }

    protected function implodeScalars(array $value): string
    {
        $wrapper = static function ($v) {
            if (is_string($v)) {
                return '"' . $v . '"';
            }

            if ($v instanceof Enum || $v instanceof UnitEnum) {
                return match (true) {
                    $v instanceof Enum => $v->key,
                    default => $v->name,
                };
            }

            return $v;
        };

        return implode(', ', array_map($wrapper, $value));
    }

    protected function normalizeObject(string $object): string
    {
        $object = trim($object, ', ');

        return str_replace(['{{', '}}'], ['{', '}'], $object);
    }

    protected function convertToGQLSelect(): string
    {
        if (empty($this->select)) {
            return '';
        }

        return '{' . $this->implodeRecursive(' ', $this->select) . '}';
    }

    protected function implodeRecursive(string $glue, iterable $parameters): string
    {
        $output = '';

        foreach ($parameters as $key => $parameter) {
            if (is_iterable($parameter)) {
                if ($parameter instanceof UnionValue) {
                    $output .= '... on ';
                }
                $output .= $key . ' {' . $this->implodeRecursive($glue, $parameter) . '} ';
            } else {
                $output .= $parameter . $glue;
            }
        }

        return trim($output, $glue);
    }

    protected function getMutation(): array
    {
        $mutation = 'mutation ';

        if ($this->hasFiles) {
            $mutation .= '(';
            foreach ($this->fileVariableNames as $key => $name) {
                if (is_array($name)) {
                    $mutation .= sprintf('%s: [Upload!]! ', $key);
                } else {
                    $mutation .= sprintf('%s: Upload! ', $key);
                }
            }

            $mutation = trim($mutation);
            $mutation .= ') ';
        }

        return ['query' => $mutation . $this->getQueryString()];
    }

    public function getQueryString(): string
    {
        $this->buildQuery();

        return $this->query;
    }

    /**
     * @throws JsonException
     */
    protected function getUploadMutation(): array
    {
        return [
            'operations' => json_encode(
                $this->getMutation(),
                JSON_THROW_ON_ERROR
            ),
            'map' => '{ ' . implode(', ', $this->map) . ' }',
        ] + $this->files;
    }

    protected function getQuery(): array
    {
        return ['query' => 'query ' . $this->getQueryString()];
    }

    public function dd(): void
    {
        $this->dump();

        exit(1);
    }

    public function dump(): self
    {
        $this->buildQuery();

        VarDumper::dump($this);

        return $this;
    }

    public function uploadMode(): void
    {
        $this->queryType = self::QUERY_TYPES['upload'];
    }

    public function isUploadMode(): bool
    {
        return $this->queryType == self::QUERY_TYPES['upload'];
    }
}
