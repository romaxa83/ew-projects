<?php

namespace App\Scribe\Extracting\Strategies\Metadata;

use Core\Permissions\BasePermission;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\RouteDocBlocker;
use Knuckles\Scribe\Extracting\Strategies\Strategy;
use Mpociot\Reflection\DocBlock;

class PermissionDocBlock extends Strategy
{
    public function __invoke(ExtractedEndpointData $endpointData, array $routeRules): ?array
    {
        $docBlocks = RouteDocBlocker::getDocBlocksFromRoute($endpointData->route);
        $methodDocBlock = $docBlocks['method'];

        return $this->getPermissionDocBlock($methodDocBlock);
    }

    protected function getPermissionDocBlock(DocBlock $docBlock): ?array
    {
        foreach ($docBlock->getTags() as $tag) {
            if ($tag->getName() === 'permission') {
                return [
                    'description' => $this->getPermissionDescription(
                        $tag->getDescription(),
                        $tag->getDocBlock()?->getLongDescription()?->getContents()
                    )
                ];
            }
        }

        return null;
    }

    protected function getPermissionDescription(string $permissionKey, ?string $blockDescription): string
    {
        if (class_exists($permissionKey) && is_a($permissionKey, BasePermission::class, true)) {
            $permissionKey = $permissionKey::KEY;
        }

        return $blockDescription
            . <<<HTML
<aside>
<b>Permission:</b> $permissionKey
</aside>
HTML;
    }
}
