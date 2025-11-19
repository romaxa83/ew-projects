<?php

declare(strict_types=1);

namespace Wezom\Core\Services;

use Carbon\Carbon;
use GraphQL\Type\Definition\Description;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use LogicException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Schema\Types\Scalars\Upload;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Throwable;
use Wezom\Core\Annotations\GraphQlHidden;
use Wezom\Core\Annotations\GraphQlType;
use Wezom\Core\GraphQL\Types\DateTimeForFront;

class GraphQlTypeGeneratingService
{
    public function __construct(private TypeRegistry $typeRegistry)
    {
    }

    /**
     * @param  class-string<Data>  $class
     *
     * @throws ReflectionException
     */
    public function generateInputFromDto(string $class, bool $autoResolve = false): ?InputObjectType
    {
        if (!is_a($class, Data::class, true)) {
            throw new InvalidArgumentException("Class $class must implement " . Data::class);
        }

        $inputName = str($class)
            ->classBasename()
            ->replaceLast('Dto', 'Input')
            ->value();

        if ($this->typeRegistry->has($inputName)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($class);

        $config = [
            'name' => $inputName,
            'description' => $this->getClassDescription($reflectionClass),
            'fields' => $this->resolveDtoFields($reflectionClass),
        ];

        if ($autoResolve) {
            $config['parseValue'] = static fn (array $values) => $class::validateAndCreate($values);
        }

        return new InputObjectType($config);
    }

    private function resolveDtoFields(ReflectionClass $reflectionClass): array
    {
        $fields = [];

        $dataClass = DataContainer::get()->dataClassFactory()->build($reflectionClass);

        $rules = $this->readRules($dataClass);

        foreach ($dataClass->properties as $property) {
            if ($this->getPropertyAttribute($property, GraphQlHidden::class)) {
                continue;
            }

            $type = $this->getPropertyType($property);
            $propertyRules = array_get($rules, $property->name, []);
            $description = (new GraphQlPropertyDescriptionGenerator($property, $type, $propertyRules))
                ->getDescription();

            $fields[] = array_filter([
                'name' => $property->name,
                'type' => $type,
                'description' => $description ?: null,
                'defaultValue' => $property->defaultValue !== null ? $property->defaultValue : null,
            ], static fn ($v) => $v !== null);
        }

        return $fields;
    }

    private function getPropertyType(DataProperty $property): Type
    {
        $propertyName = $property->name;

        $graphQlType = $this->getPropertyAttribute($property, GraphQlType::class)?->type;

        if ($iterableItemType = $property->type->iterableItemType) {
            $propertyTypeName = $graphQlType ?? $iterableItemType;

            $type = $this->resolveType($propertyName, $propertyTypeName);

            if (!$type) {
                throw new LogicException(
                    "Cant guess type for {$property->className}::{$propertyName}, iterable type: $propertyTypeName"
                );
            }

            $type = Type::listOf(Type::nonNull($type));
        } else {
            /** @phpstan-ignore-next-line  */
            $propertyTypeName = $graphQlType ?? $property->type->type->name;

            $type = $this->resolveType($propertyName, $propertyTypeName);
            if (!$type) {
                throw new LogicException(
                    "Cant guess type for {$property->className}::{$propertyName}, type $propertyTypeName"
                );
            }
        }

        if (!$property->type->isNullable) {
            $type = Type::nonNull($type);
        }

        return $type;
    }

    private function resolveType(string $propertyName, string $propertyTypeName): ?Type
    {
        return $this->resolveSimpleType($propertyName, $propertyTypeName)
            ?? $this->findRegisteredType($propertyTypeName);
    }

    private function resolveSimpleType(string $propertyName, string $propertyTypeName): ?Type
    {
        $type = null;
        switch (mb_strtolower($propertyTypeName)) {
            case 'bool':
            case 'boolean':
                $type = Type::boolean();
                break;
            case 'float':
                $type = Type::float();
                break;
            case 'int':
            case 'integer':
                $type = Type::int();
                break;
            case 'string':
                $type = Type::string();
                break;
        }

        if (!$type) {
            return null;
        }

        $isId = str($propertyName)
            ->snake(' ')
            ->lower()
            ->explode(' ')
            ->intersect(['id', 'ids'])
            ->isNotEmpty();

        return ($type instanceof IntType || $type instanceof StringType) && $isId ? Type::id() : $type;
    }

    private function findRegisteredType($name): ?Type
    {
        switch ($name) {
            case Carbon::class:
            case \Illuminate\Support\Carbon::class:
                $name = DateTimeForFront::class;
                break;
            case UploadedFile::class:
                $name = Upload::class;
                break;
        }

        $baseName = str($name)->classBasename()->value();

        $nameVariations = [$baseName, str($baseName)->replaceLast('Dto', 'Input')->value()];
        foreach ($nameVariations as $name) {
            if ($this->typeRegistry->has($name)) {
                return $this->typeRegistry->get($name);
            }
        }

        return null;
    }

    private function getClassDescription(ReflectionClass $reflectionClass): ?string
    {
        /** @var ReflectionAttribute<Description>|null $attribute */
        $attribute = array_first($reflectionClass->getAttributes(Description::class));

        return $attribute?->newInstance()->description;
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $class
     * @return T|null
     */
    private function getPropertyAttribute(DataProperty $property, string $class): mixed
    {
        return $property->attributes
            ->filter(fn ($attribute) => is_a($attribute, $class, true))
            ->first();
    }

    private function readRules(DataClass $dataClass): array
    {
        $targetClassName = $dataClass->name;
        if (!method_exists($targetClassName, 'rules')) {
            return [];
        }

        try {
            $validationContext = new ValidationContext([], [], ValidationPath::create());

            return $targetClassName::rules($validationContext);
        } catch (Throwable $e) {
            logger()->error($e->getMessage(), ['e' => $e]);
        }

        return [];
    }
}
