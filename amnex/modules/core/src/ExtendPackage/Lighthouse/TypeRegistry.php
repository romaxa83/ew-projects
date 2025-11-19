<?php

namespace Wezom\Core\ExtendPackage\Lighthouse;

use Closure;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Nuwave\Lighthouse\Schema\TypeRegistry as BaseTypeRegistry;
use Nuwave\Lighthouse\Schema\Values\TypeValue;

class TypeRegistry extends BaseTypeRegistry
{
    /**
     * Returns a closure that lazy loads the fields for a constructed type.
     *
     * @return Closure(): array<string, Closure(): array<string, mixed>>
     */
    protected function makeFieldsLoader(ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $typeDefinition): Closure
    {
        return function () use ($typeDefinition): array {
            $fieldFactory = $this->fieldFactory();
            $typeValue = new TypeValue($typeDefinition);
            $fields = [];

            foreach ($typeDefinition->fields as $fieldDefinition) {
                $fields[$fieldDefinition->name->value] = static fn (): array => $fieldFactory->handle(
                    new FieldValue($typeValue, $fieldDefinition),
                );
            }

            return $fields;
        };
    }
}
