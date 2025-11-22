<?php

namespace Tests\Unit\Services\FileBrowser\Actions;

use App\Dto\FileBrowser\FileUploadDto;
use App\Services\FileBrowser\Actions\FileUploadAction;
use ReflectionException;
use ReflectionMethod;
use Tests\Feature\Http\Api\V1\Common\FileBrowser\AbstractFileBrowserTest;

/**
 * @group FileBrowser
 */
class FileUploadActionTest extends AbstractFileBrowserTest
{
    /**
     * @param $name
     * @param $expected
     * @dataProvider itReplaceSpecialCharacterDataProvider
     * @throws ReflectionException
     */
    public function test_it_replace_special_character($name, $expected)
    {
        $dto = FileUploadDto::byParams('default', '');

        $action = new FileUploadAction($dto);

        $method = $this->setMethodAsPublic($action, 'replaceSpecialCharacters');

        $this->assertEquals($expected, $method->invoke($action, $name));
    }

    public static function itReplaceSpecialCharacterDataProvider()
    {
        return [
            ['some microsoft xls file.xls', 'some-microsoft-xls-file.xls'],
            ['some microsoft xls file.docx', 'some-microsoft-xls-file.docx'],
        ];
    }

    public function setMethodAsPublic($object, string $methodName)
    {
        $method = new ReflectionMethod($object, $methodName);

        $method->setAccessible(true);

        return $method;
    }
}
