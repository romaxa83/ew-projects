<?php

namespace App\Documents;

use App\Documents\Filters\DocumentFilter;
use App\Http\Controllers\Api\Helpers\ES\ElasticsearchBuilderTrait;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Throwable;

abstract class Document implements Arrayable
{
    use ElasticsearchBuilderTrait;

    private static array $properties = [];
    protected string $index;
    protected array $sort;
    protected array $query;
    protected array $body = [];
    protected int $size = 10;
    protected int $from = 0;
    private array $attributes = [];

    public static function init(): self
    {
        return new static();
    }

    /**
     * @param string $name
     * @param $arguments
     * @return string
     * @throws Exception
     */
    public static function __callStatic(string $name, $arguments)
    {
        if (!array_key_exists(static::class, self::$properties)) {
            $properties = (new ReflectionClass(static::class))
                ->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($properties as $property) {
                self::$properties[static::class][] = $property->getName();
            }
        }
        if (!in_array($name, self::$properties[static::class])) {
            throw new Exception("Attribute [%s] not found", $name);
        }
        return $name !== 'id' ? Str::snake($name) : '_id';
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function find(int $id): ?self
    {
        return static::query()
            ->addBoolQuery(
                DocumentFilter::MUST,
                [
                    'term' => [
                        '_id' => $id
                    ]
                ]
            )
            ->search()
            ->first();
    }

    public static function getAllId()
    {
        return static::query()
            ->size(1000)
            ->search()
            ->pluck('id')
            ->toArray()
            ;
    }

    public static function getAllIdBySize(int $size = 1000)
    {
        return static::query()
            ->size($size)
            ->search()
            ->pluck('id')
            ->toArray()
            ;
    }

    public function addBoolQuery(string $type, array $args): self
    {
        $this->query = DocumentFilter::makeBoolQuery($this->query, $type, $args);
        return $this;
    }

    public static function query(): self
    {
        $document = new static();
        $document->sort = [];
        $document->query = [];
        $document->body = [];
        $document->size = 10;
        $document->from = 0;
        return $document;
    }

    public function __get(string $name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            throw new RuntimeException(sprintf("Property [%s] not found in class [%s]", $name, static::class));
        }
        return $this->attributes[$name];
    }

    public function save(): bool
    {
        $data = [
            'body' => $this->toArray(),
            'index' => $this->getIndex(),
        ];
        if (!empty($data['body']['id'])) {
            $data['id'] = $data['body']['id'];
        }
        $result = self::esClient()->index($data);

        if (empty($result['result']) || !in_array($result['result'], ['created', 'updated'])) {
            return false;
        }
        return true;
    }

    public function toArray(): array
    {
        $class = new ReflectionClass($this);
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        $result = [];
        foreach ($properties as $property) {
            if (!$this->isFillable($property)) {
                continue;
            }
            $propertyName = Str::snake($property->getName());
            $result[$propertyName] = $this->getPropertyValue($property);
        }
        return $result;
    }

    private function isFillable(ReflectionProperty $property): bool
    {
        $doc = $property->getDocComment();
        if (empty($doc)) {
            return true;
        }
        return !preg_match("/@es-ignore/", $doc);
    }

    private function getPropertyValue(ReflectionProperty $property)
    {
        if (!$property->isInitialized($this)) {
            return null;
        }
        $value = $property->getValue($this);
        if ($value instanceof Carbon) {
            $phpDoc = $property->getDocComment();
            if (empty($phpDoc)) {
                return $value->getTimestamp();
            }
            if (preg_match("/@es-date-time/", $phpDoc)) {
                return $value->toIso8601ZuluString();
            }
            if (preg_match("/@es-date/", $phpDoc)) {
                return $value->toDateString();
            }
            return $value->getTimestamp();
        }
        return $value;
    }

    protected function getIndex(): string
    {
        if (isset($this->index)) {
            return $this->index;
        }
        $index = preg_replace("/document$/i", "", class_basename(static::class));
        $this->index = config('database.es.index_prefix') . Str::plural(Str::snake($index));
        return $this->index;
    }

    public function addBodyData(array $data): self
    {
        $this->body = array_merge($this->body, $data);
        return $this;
    }

    public function sort(string $field, string $direction = 'asc'): self
    {
        if ($field === '_id') {
            $field = 'id';
        }
        $this->sort[] = [
            $field => $direction
        ];
        return $this;
    }

    public function size(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function from(int $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function aggregation(array $aggregation): array
    {
        $aggs = $this->runSearch($aggregation, true);
        $result = [];
        foreach ($aggs as $key => $item) {
            $result[$key] = $item['value'];
        }
        return $result;
    }

    private function runSearch(array $fields, bool $aggregation = false): array
    {
        $body = array_merge(
            [
                'query' => $this->query,
                'sort' => $this->sort,
                'size' => $this->size,
                'from' => $this->from
            ],
            $this->body
        );
        if ($aggregation) {
            $body['aggs'] = $fields;
            unset($fields);
        }
        if (!empty($fields)) {
            $index = array_search('id', $fields);
            if ($index !== false) {
                unset($fields[$index]);
            }
            if (empty($fields)) {
                $body['_source'] = false;
            } else {
                $body['_source'] = array_values($fields);
            }
        }
        if (empty($body['query'])) {
            unset($body['query']);
        }
        $result = self::esClient()->search(
            [
                'index' => $this->getIndex(),
                'body' => $body
            ]
        );
        if ($aggregation) {
            return array_merge(
                !empty($result['aggregations']) ? $result['aggregations'] : [],
                [
                    'total' => [
                        'value' => $result['hits']['total']['value']
                    ]
                ]
            );
        }
        return !empty($result['hits']['hits']) ? $result['hits']['hits'] : [];
    }

    public function search(array $fields = []): Collection
    {
        $result = $this->runSearch($fields);
        if (empty($result)) {
            return collect();
        }
        $documents = collect();
        $reflection = (new ReflectionClass(static::class));
        foreach ($result as $item) {
            $document = new static();
            $document->id = $item['_id'];
            foreach ($item['_source'] as $key => $value) {
                $this->setPropertyValue($document, $key, $value, $reflection);
            }
            if (!empty($item['fields'])) {
                foreach ($item['fields'] as $key => $value) {
                    $this->setPropertyValue($document, $key, $value[0], $reflection);
                }
            }
            $documents->push($document);
        }
        return $documents;
    }

    private function setPropertyValue(self $document, string $key, $value, ReflectionClass $reflection): void
    {
        $propertyName = Str::camel($key);
        try {
            $type = $reflection->getProperty($propertyName)->getType();
        } catch (Throwable $e) {
            $document->attributes[$key] = $value;
            return;
        }
        if ($type->getName() === Carbon::class && !is_null($value)) {
            $value = is_int($value) ? Carbon::createFromTimestamp($value) : Carbon::parse($value);
        }
        $document->{$propertyName} = $value;
    }

    public function searchIds(bool $int = true): array
    {
        $result = $this->runSearch(['id']);
        if (empty($result)) {
            return [];
        }
        $ids = array_column($result, '_id');
        return $int ? array_map('intval', $ids) : $ids;
    }

    public function delete($id): void
    {
        try {
            self::esClient()
                ->delete(
                    [
                        'index' => $this->getIndex(),
                        'id' => $id
                    ]
                );
        } catch (Missing404Exception $e) {
        }
    }

    public function all()
    {
        try {
            return self::esClient()
                ->get(
                    [
                        'index' => $this->getIndex(),
                    ]
                );
        } catch (Missing404Exception $e) {
        }
    }

    public function count(): int
    {
        $body = [];
        if (!empty($this->query)) {
            $body['query'] = $this->query;
        }
        $result = self::esClient()
            ->count(
                [
                    'index' => $this->getIndex(),
                    'body' => $body
                ]
            );
        return !empty($result['count']) ? $result['count'] : 0;
    }
}
