<?php

namespace Tests\Unit\Dto\Catalog\Troubleshoots;

use App\Dto\Catalog\Troubleshoots\TroubleshootDto;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class TroubleshootDtoTest extends TestCase
{
    use TestStorage;

    /** @test */
    public function success_fill_by_args()
    {
        $this->fakeMediaStorage();

        $data = static::data();
        $data['pdf'] = $this->getSamplePdf();

        $dto = TroubleshootDto::byArgs($data);

        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getGroupId(), $data['group_id']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getPdf(), $data['pdf']);
    }

    /** @test */
    public function success_fill_by_args_without_pdf()
    {
        $data = static::data();

        $dto = TroubleshootDto::byArgs($data);

        $this->assertNull($dto->getPdf());
    }

    /** @test */
    public function success_fill_by_args_without_active()
    {
        $data = static::data();
        unset($data['active']);

        $dto = TroubleshootDto::byArgs($data);

        $this->assertEquals($dto->getActive(), Troubleshoot::DEFAULT_ACTIVE);
    }

    public static function data()
    {
        return [
            'sort' => 2,
            'active' => false,
            'group_id' => 22,
            'product_id' => 22,
            'name' => 'troubleshoots name',
        ];
    }
}


