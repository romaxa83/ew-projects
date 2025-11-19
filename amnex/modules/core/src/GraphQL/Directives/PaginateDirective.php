<?php

namespace Wezom\Core\GraphQL\Directives;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\StringValueNode;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Pagination\PaginationManipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Values\TypeValue;
use Nuwave\Lighthouse\Support\Utils;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Wezom\Core\ExtendPackage\Lighthouse\FieldValue;
use Wezom\Core\GraphQL\BaseFieldResolver;

class PaginateDirective extends \Nuwave\Lighthouse\Pagination\PaginateDirective
{
    /**
     * @throws ReflectionException
     * @throws DefinitionException
     */
    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType
    ): void {
        $this->validateMutuallyExclusiveArguments(['model', 'builder', 'resolver']);

        $paginationManipulator = new PaginationManipulator($documentAST);

        if ($this->directiveHasArgument('resolver')) {
            // This is done only for validation
            $this->getResolverFromArgument('resolver');
        } elseif ($this->directiveHasArgument('builder')) {
            // This is done only for validation
            $this->getResolverFromArgument('builder');
        } elseif (!$this->autoRegisterResolverOrBuilder($fieldDefinition, $parentType)) {
            $paginationManipulator->setModelClass($this->getModelClass());
        }

        $paginationManipulator->transformToPaginatedField(
            $this->paginationType(),
            $fieldDefinition,
            $parentType,
            $this->defaultCount(),
            $this->paginateMaxCount(),
        );
    }

    /**
     * @throws ReflectionException
     */
    private function autoRegisterResolverOrBuilder(
        FieldDefinitionNode $fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $parentType
    ): bool {
        $fieldValue = new FieldValue(new TypeValue($parentType), $fieldDefinition);

        // Search resolver
        $resolver = Utils::namespaceClassname(
            Str::studly($fieldValue->getFieldName()),
            $fieldValue->parentNamespaces(),
            static fn (string $class): bool => method_exists($class, '__invoke'),
        );

        if ($resolver === null) {
            return false;
        }

        $methodName = match (true) {
            is_a($resolver, BaseFieldResolver::class, true) => 'resolve',
            default => '__invoke'
        };

        $methodReflection = new ReflectionMethod($resolver, $methodName);

        /** @var ReflectionNamedType|ReflectionUnionType $returnType */
        $returnType = $methodReflection->getReturnType();

        $returnTypes = $returnType instanceof ReflectionUnionType ? $returnType->getTypes() : [$returnType];

        $argumentName = null;
        foreach ($returnTypes as $item) {
            $argumentName = match (true) {
                is_a($item->getName(), Builder::class, true) => 'builder',
                is_a($item->getName(), Paginator::class, true) => 'resolver',
                default => null
            };
            if ($argumentName !== null) {
                break;
            }
        }

        if ($argumentName === null) {
            return false;
        }

        foreach ($fieldDefinition->directives as $directive) {
            /** @var DirectiveNode $directive */
            if ($directive->name->value !== $this->directiveNode->name->value) {
                continue;
            }

            $directive->arguments[$argumentName] = new ArgumentNode([
                'name' => new NameNode(['value' => $argumentName]),
                'value' => new StringValueNode(['value' => $resolver]),
            ]);
            break;
        }

        return true;
    }
}
