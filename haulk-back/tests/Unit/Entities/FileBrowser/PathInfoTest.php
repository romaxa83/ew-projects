<?php

namespace Tests\Unit\Entities\FileBrowser;

use App\Entities\FileBrowser\PathInfo;
use Tests\TestCase;

/**
 * @group FileBrowser
 */
class PathInfoTest extends TestCase
{
    /**
     * @param $path
     * @param $expected
     * @dataProvider itGetExtensionSuccessDataProvider
     */
    public function test_it_get_extension_success($path, $expected)
    {
        $this->assertEquals($expected, PathInfo::byPath($path)->getExtension());
    }

    public function itGetExtensionSuccessDataProvider()
    {
        return [
            ['some path/directory/file.extension', 'extension'],
            ['some path/directory/file.pdf', 'pdf'],
            ['some path/directory/file', ''],
        ];
    }
}
