<?php

namespace WezomCms\Core\Tests\Unit;

use Tests\TestCase;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Foundation\Assets\Items\InlineAssetItem;
use WezomCms\Core\Foundation\Assets\Items\LocalFileAssetItem;
use WezomCms\Core\Foundation\Assets\Items\MixAssetItem;
use WezomCms\Core\Foundation\Assets\WezomAssetManager;

class WezomAssetManagerTest extends TestCase
{
    /**
     * @var AssetManagerInterface
     */
    protected $assetManager;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->assetManager = new WezomAssetManager();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function testAddJsByPath()
    {
        $this->assetManager->addJs($this->getJsPath());

        $actual = $this->assetManager->getJs()[0];

        $expected = new LocalFileAssetItem();
        $expected->setContent($this->getJsPath());
        $expected->setType(LocalFileAssetItem::TYPE_JS);

        $this->assertEquals($expected->getContent(), $actual->getContent());
        $this->assertEquals($expected->toHtml(), $actual->toHtml());
    }

    public function testThrowExceptionWhenNotAllowedItemType()
    {
        $expected = new LocalFileAssetItem();
        $expected->setContent($this->getJsPath());

        $this->expectException(\Exception::class);
        $expected->toHtml();
    }

    public function testCombineAttributesAndToStringMethod()
    {
        $attributes = ['defer' => 'defer'];

        $item = new LocalFileAssetItem();
        $item->setContent($this->getJsPath());
        $item->setAttributes($attributes);
        $item->setType(LocalFileAssetItem::TYPE_JS);

        $this->assertStringContainsString('defer="defer"', (string) $item);
    }

    public function testAddJsByAssetItem()
    {
        $assetItem = new LocalFileAssetItem();
        $assetItem->setContent($this->getJsPath());

        $this->assetManager->addJs($assetItem);

        $item = $this->assetManager->getJs()[0];

        $this->assertEquals($assetItem->getContent(), $item->getContent());
    }

    public function testAddJsWithName()
    {
        $this->assetManager->addJs($this->getJsPath(), 'bar');

        $item = $this->assetManager->getJs()[0];

        $this->assertEquals('bar', $item->getName());
    }

    public function testAddJsWithAttributes()
    {
        $attributes = ['data-foo' => 'bar', 'rel' => 'internal'];

        $this->assetManager->addJs($this->getJsPath(), '', $attributes);

        $item = $this->assetManager->getJs()[0];

        $this->assertEquals($attributes, $item->getAttributes());
    }

    public function testAddCssByPath()
    {
        $this->assetManager->addCss($this->getCssPath());

        $item = $this->assetManager->getCss()[0];

        $expected = new LocalFileAssetItem();
        $expected->setContent($this->getCssPath());
        $expected->setType(LocalFileAssetItem::TYPE_CSS);

        $this->assertEquals($expected->getContent(), $item->getContent());
        $this->assertEquals($expected->toHtml(), $item->toHtml());
    }

    public function testAddCssByAssetItem()
    {
        $assetItem = new LocalFileAssetItem();
        $assetItem->setContent($this->getCssPath());

        $this->assetManager->addCss($assetItem);

        $item = $this->assetManager->getCss()[0];

        $this->assertEquals($assetItem->getContent(), $item->getContent());
    }

    public function testAddInlineScript()
    {
        $script = 'alert("Hello!");';

        $this->assetManager->addInlineScript($script);

        $item = $this->assetManager->getJs()[0];

        $inlineAssetItem = new InlineAssetItem();
        $inlineAssetItem->setContent($script);

        $expectedHtml = '<script >' . $inlineAssetItem->getContent() . '</script>';

        $this->assertEquals($expectedHtml, $item->toHtml());
        $this->assertEquals($inlineAssetItem->getContent(), $item->getContent());
    }

    public function testThrowExceptionWhenNotAllowedItemTypeInlineAsset()
    {
        $expected = new InlineAssetItem();
        $expected->setContent('script');

        $this->expectException(\Exception::class);
        $expected->toHtml();
    }


    public function testAddInlineStyle()
    {
        $style = 'body {margin:0;padding:0}';

        $this->assetManager->addInlineStyle($style);

        $item = $this->assetManager->getCss()[0];

        $inlineAssetItem = new InlineAssetItem();
        $inlineAssetItem->setContent($style);

        $expectedHtml = '<style type="text/css" >' . $inlineAssetItem->getContent() . '</style>';

        $this->assertEquals($expectedHtml, $item->toHtml());
        $this->assertEquals($inlineAssetItem->getContent(), $item->getContent());
    }

    public function testGetInlineScripts()
    {
        $script = 'alert("Hello!");';

        $this->assetManager->addInlineScript($script);
        $this->assetManager->addJs($this->getJsPath());

        $items = $this->assetManager->getInlineScripts();

        $inlineAssetItem = new InlineAssetItem();
        $inlineAssetItem->setContent($script);

        $this->assertTrue(count($items) === 1);
        $this->assertEquals($inlineAssetItem->getContent(), $items[0]->getContent());
    }

    public function testGetInlineStyles()
    {
        $style = 'body {margin:0;padding:0}';

        $this->assetManager->addInlineStyle($style);
        $this->assetManager->addCss($this->getCssPath());

        $items = $this->assetManager->getInlineStyles();

        $inlineAssetItem = new InlineAssetItem();
        $inlineAssetItem->setContent($style);

        $this->assertTrue(count($items) === 1);
        $this->assertEquals($inlineAssetItem->getContent(), $items[0]->getContent());
    }

    public function testSetPositionForJs()
    {
        $this->assetManager->addJs($this->getJsPath())
            ->position(AssetManagerInterface::POSITION_END_BODY);
        $this->assetManager->addJs($this->getJsPath());

        $items = $this->assetManager->getJs(AssetManagerInterface::POSITION_END_BODY);

        $this->assertTrue(count($items) === 1);
    }

    public function testSetPositionForCss()
    {
        $this->assetManager->addCss($this->getCssPath())
            ->position(AssetManagerInterface::POSITION_START_BODY);
        $this->assetManager->addCss($this->getCssPath());

        $items = $this->assetManager->getCss(AssetManagerInterface::POSITION_START_BODY);

        $this->assertTrue(count($items) === 1);
    }

    public function testSetPositionForNotExistingLastItem()
    {
        $this->expectException(\LogicException::class);

        $this->assetManager->position(AssetManagerInterface::POSITION_END_BODY);
    }

    public function testFailureMixAssetItem()
    {
        $item = new MixAssetItem();
        $item->setContent($this->getCssPath());

        $res = $item->getContent();

        $this->assertEquals('', $res);
    }

    private function getJsPath()
    {
        return 'temp/foo.js';
    }

    private function getCssPath()
    {
        return 'temp/foo.css';
    }
}
