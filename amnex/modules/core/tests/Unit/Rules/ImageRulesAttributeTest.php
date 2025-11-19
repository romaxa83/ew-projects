<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Rules;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

use PHPUnit\Framework\Attributes\Test;
use Wezom\Core\Enums\Images\ImageExtension;
use Wezom\Core\Rules\ImageRulesAttribute;
use Wezom\Core\Testing\TestCase;

class ImageRulesAttributeTest extends TestCase
{
    #[Test]
    public function makeRulesWithDefaultParameters(): void
    {
        $this->checkRuleParameters(
            new ImageRulesAttribute(),
            ImageRulesAttribute::DEFAULT_EXTENSIONS,
            ImageRulesAttribute::DEFAULT_SIZE,
        );
    }

    private function checkRuleParameters(ImageRulesAttribute $container, array $mimes, int $maxSize): void
    {
        assertCount(3, $container->get());

        assertEquals($maxSize, $container->getMaxSize());
        assertEquals($mimes, $container->getExtensions());
    }

    #[Test]
    public function makeRulesWithSpecificParameters(): void
    {
        $extensions = [ImageExtension::JPEG];
        $size = 74;

        $this->checkRuleParameters(
            new ImageRulesAttribute($extensions, $size),
            $extensions,
            $size,
        );
    }
}
