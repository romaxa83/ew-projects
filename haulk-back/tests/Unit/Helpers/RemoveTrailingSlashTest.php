<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class RemoveTrailingSlashTest extends TestCase
{
    /**
     * @param $path
     * @param $expected
     * @dataProvider itNormalizePathSuccessDataProvider
     */
    public function test_it_remove_slashes_from_path_success($path, $expected)
    {
        $this->assertEquals($expected, remove_trailing_slashes($path));
    }

    public function itNormalizePathSuccessDataProvider()
    {
        return [
            ['/var/local/bin/', 'var/local/bin'],
            ['/var/local/bin', 'var/local/bin'],
            ['var/local/bin/', 'var/local/bin'],
            ['var/local/bin', 'var/local/bin'],
            ['/', ''],
            [null, ''],
        ];
    }
}
