<?php

namespace App\Scribe\Extracting\Strategies\Metadata;

use Core\Enums\BaseEnum;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\RouteDocBlocker;
use Knuckles\Scribe\Extracting\Strategies\Strategy;
use Mpociot\Reflection\DocBlock;

class EnumBockBlock extends Strategy
{
    public function __invoke(ExtractedEndpointData $endpointData, array $routeRules): array
    {
        $docBlocks = RouteDocBlocker::getDocBlocksFromRoute($endpointData->route);
        $classDocBlock = $docBlocks['class'];

        return $this->getEnumDocBlock($classDocBlock);
    }

    protected function getEnumDocBlock(DocBlock $docBlock): array
    {
        $enumDescriptions = [];
        $enumDescription = '';

        foreach ($docBlock->getTags() as $tag) {
            if ($tag->getName() === 'enum') {
                $enumDescriptions[] = $this->getEnumDescription($tag->getDescription());
            }
        }

        if (count($enumDescriptions)) {
            $enumDescription = "<aside>Enums: " . implode('', $enumDescriptions) . "</aside>";
        }

        return [
            'groupDescription' => $docBlock->getLongDescription()?->getContents() . $enumDescription
        ];
    }

    protected function getEnumDescription(string $enumClass): string
    {
        if (class_exists($enumClass) && is_a($enumClass, BaseEnum::class, true)) {
            return $this->buildEnumDescriptionByClass($enumClass);
        }

        return '';
    }

    protected function buildEnumDescriptionByClass(string|BaseEnum $enumClass): string
    {
        $values = $enumClass::getValues();

        $name = class_basename($enumClass);

        $description = '<table>';
        $description .= $this->getTableHead($name);
        $description .= '<tbody>';

        foreach ($values as $value) {
            $description .= $this->getTableRow($value);
        }

        $description .= '</tbody>';
        $description .= '</table>';

        return $description;
    }

    protected function getTableHead(string $enumName): string
    {
        return "<thead><tr><th>$enumName</th></tr></thead>";
    }

    protected function getTableRow(string $enumValue): string
    {
        return "<tr><td>$enumValue</td></tr>";
    }
}
