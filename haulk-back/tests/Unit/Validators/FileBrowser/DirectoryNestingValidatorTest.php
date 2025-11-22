<?php

namespace Tests\Unit\Validators\FileBrowser;

use App\Validators\FileBrowser\DirectoryNestingValidator;
use Tests\TestCase;

/**
 * @group FileBrowser
 */
class DirectoryNestingValidatorTest extends TestCase
{
    /**
     * @param $path
     * @param $nestingLimit
     * @param $expected
     * @dataProvider itValidateNestingPathDataProvider
     */
    public function test_it_validate_nesting_path($path, $nestingLimit, $expected)
    {
        $validator = new DirectoryNestingValidator($nestingLimit);

        $this->assertEquals($expected, $validator->passes('attribute', $path));
    }

    public function itValidateNestingPathDataProvider()
    {
        return [
            '2 allowed, 1 passed' => ['/path', 2, true],
            '2 allowed, 3 passed' => ['/path/sub/sub', 2, false],
            '1 allowed, 1 passed' => ['/path', 1, false],
            '1 allowed, 2 passed' => ['/path/subpath', 1, false],
            '2 allowed, 2 passed' => ['/path/subpath', 2, false],
            '3 allowed, 2 passed' => ['/path/subpath', 3, true],
        ];
    }
}
