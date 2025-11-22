<?php

namespace Tests\Unit\Validators\FileBrowser;

use App\Validators\FileBrowser\PathValidator;
use Tests\TestCase;

/**
 * @group FileBrowser
 */
class PathValidatorTest extends TestCase
{
    /**
     * @param $value
     * @param $expected
     * @dataProvider itValidatePathDataProvider
     */
    public function test_it_validate_path($value, $expected)
    {
        $validator = new PathValidator();

        $this->assertEquals($expected, $validator->passes('path', $value));
    }

    public function itValidatePathDataProvider(): array
    {
        return [
            ['/../', false],
            ['..', false],
            ['some_dir', true],
            ['some_dir/..', true],
            ['some_dir/../../', false],
            ['some_dir/some_dir2/../../', true],
            ['some_dir/some_dir2/../../', true],
            ['some_dir/some_dir2/../../..', false],
        ];
    }
}
