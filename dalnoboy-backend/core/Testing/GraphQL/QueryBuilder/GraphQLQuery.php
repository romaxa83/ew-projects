<?php

declare(strict_types=1);

namespace Core\Testing\GraphQL\QueryBuilder;

use Core\Enums\BaseEnum;
use Core\Testing\GraphQL\Scalar\Scalar;
use Core\ValueObjects\AbstractValueObject;
use Illuminate\Http\UploadedFile;
use JsonException;

class GraphQLQuery
{
    use GraphQLBuilderTrait;

    protected string $queryType = 'query';

    protected array $args;
    protected array $select;
    protected string $name;
    protected string $query;

    protected bool $hasFiles = false;
    protected array $map = [];
    protected array|UploadedFile|null $files = null;

    protected array $fileVariableNames;

    public function __construct(string $name, array $args = [], array $select = [])
    {
        $this->name = $name;
        $this->args = $args;
        $this->select = $select;

        $this->buildQuery();
    }

    protected function buildQuery(): void
    {
        $this->query = '{' . $this->name . $this->convertToGQLArgs() . $this->convertToGQLSelect() . '}';
    }

    protected function convertToGQLArgs(): string
    {
        if (count($this->args) === 0) {
            return '';
        }

        $argsString = '(';

        foreach ($this->args as $key => $value) {
            $argsString .= $this->resolveValue($key, $value);
        }

        $argsString = trim($argsString, ', ');

        $argsString .= ')';

        return $argsString;
    }

    protected function resolveValue(int|string $key, mixed $value): string
    {
        if (is_null($value)) {
            return $this->resolveNullValue($key, $value);
        }

        if ($this->isScalar($value)) {
            return $this->resolveScalarValue($key, $value);
        }

        if ($value instanceof UploadedFile || $this->arrayIsListOfFiles($value)) {
            return $this->resolveFiles($key, $value);
        }

        $object = $this->resolveArrayValue($value);

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
            || $value instanceof Scalar
            || $value instanceof BaseEnum
            || $value instanceof AbstractValueObject;
    }

    protected function resolveScalarValue(int|string $key, mixed $value): string
    {
        return $key . ': ' . $this->toGraphQLValue($value) . ', ';
    }

    protected function resolveNullValue(int|string $key, mixed $value): string
    {
        return $key . ':null, ';
    }

    protected function toGraphQLValue(mixed $var): string|float|int
    {
        if ($var instanceof BaseEnum) {
            return $var->value;
        }

        if ($var instanceof Scalar) {
            return (string)$var;
        }

        if (is_int($var) || is_float($var)) {
            return $var;
        }

        if (is_bool($var)) {
            return $var ? 'true' : 'false';
        }

        return '"' . $var . '"';
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

    protected function resolveFiles(string $key, array|UploadedFile $value): string
    {
        $this->hasFiles = true;

        $this->map[] = sprintf('"%s": ["variables.%s"]', $key, $key);

        $fileVariableKey = $key;
        $fileVariableName = '$' . $fileVariableKey;

        $this->files[$fileVariableKey] = $value;
        $this->fileVariableNames[] = $fileVariableName;

        if (is_array($value)) {
            $fileVariable = "[$fileVariableName]";
        } else {
            $fileVariable = $fileVariableName;
        }

        return sprintf('%s: %s, ', $key, $fileVariable);
    }

    protected function resolveArrayValue(array $value): string
    {
        $object = '';

        if ($this->arrayIsListOfScalars($value)) {
            return $this->implodeScalars($value);
        }

        foreach ($value as $k => $v) {
            $object .= $this->resolveValue($k, $v);
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

            if (!is_scalar($v) && !$v instanceof Scalar && !$v instanceof BaseEnum) {
                return false;
            }
        }

        return true;
    }

    protected function implodeScalars(array $value): string
    {
        $wrapper = static function ($v)
        {
            if (is_string($v)) {
                return '"' . $v . '"';
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
        if (count($this->select) === 0) {
            return '';
        }

        return '{' . $this->implodeRecursive(' ', $this->select) . '}';
    }

    public static function query(string $name): self
    {
        $self = new self($name);
        $self->queryType = 'query';

        return $self;
    }

    public static function mutation(string $name): self
    {
        $self = new self($name);
        $self->queryType = 'mutation';

        return $self;
    }

    public static function upload(string $name): self
    {
        $self = new self($name);
        $self->queryType = 'upload';

        return $self;
    }

    public function args(array $args = []): self
    {
        $this->args = $args;

        return $this;
    }

    public function select(array $select = []): self
    {
        $this->select = $select;

        return $this;
    }

    public function make(): array
    {
        $this->buildQuery();

        return match ($this->queryType) {
            'mutation' => $this->getMutation(),
            'upload' => $this->getUploadMutation(),
            default => $this->getQuery(),
        };
    }

    public function getMutation(): array
    {
        $mutation = 'mutation ';

        if ($this->hasFiles) {
            $mutation .= '(';
            foreach ($this->fileVariableNames as $name) {
                $mutation .= sprintf('%s: Upload! ', $name);
            }
            $mutation = trim($mutation);
            $mutation .= ') ';
        }

        return ['query' => $mutation . $this->query];
    }

    /**
     * @throws JsonException
     */
    public function getUploadMutation(): array
    {
        return [
                'operations' => json_encode($this->getMutation(), JSON_THROW_ON_ERROR),
                'map' => '{ ' . implode(', ', $this->map) . ' }',
            ] + $this->files;
    }

    public function getQuery(): array
    {
        return ['query' => 'query ' . $this->query];
    }

    public function __toString(): string
    {
        return $this->query;
    }
}
