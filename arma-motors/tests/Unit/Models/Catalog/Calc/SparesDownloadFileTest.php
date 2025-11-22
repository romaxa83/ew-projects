<?php

namespace Tests\Unit\Models\Catalog\Calc;

use App\Models\Catalogs\Calc\Spares;
use App\Models\Catalogs\Calc\SparesDownloadFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SparesDownloadFileTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create_success()
    {
        $type = Spares::TYPE_VOLVO;
        $fileName = 'file_name.txt';
        $model = SparesDownloadFile::createRecord($type, $fileName);

        $this->assertEquals($model->type, $type);
        $this->assertEquals($model->file_name, $fileName);
        $this->assertEquals($model->status, SparesDownloadFile::STATUS_DRAFT);
        $this->assertNull($model->error_message);
    }

    /** @test */
    public function create_wrong_type()
    {
        $type = 'wrong';
        $fileName = 'file_name.txt';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.not valid car type for spares', ['type' => 'wrong']));

        SparesDownloadFile::createRecord($type, $fileName);
    }

    /** @test */
    public function toggle_process_status()
    {
        $type = Spares::TYPE_VOLVO;
        $fileName = 'file_name.txt';
        $model = SparesDownloadFile::createRecord($type, $fileName);

        $this->assertEquals($model->status, SparesDownloadFile::STATUS_DRAFT);

        $model = $model->toggleStatusProcess();

        $this->assertEquals($model->status, SparesDownloadFile::STATUS_PROCESS);
    }

    /** @test */
    public function toggle_done_status()
    {
        $type = Spares::TYPE_VOLVO;
        $fileName = 'file_name.txt';
        $model = SparesDownloadFile::createRecord($type, $fileName);

        $this->assertEquals($model->status, SparesDownloadFile::STATUS_DRAFT);

        $model = $model->toggleStatusDone();

        $this->assertEquals($model->status, SparesDownloadFile::STATUS_DONE);
    }

    /** @test */
    public function toggle_error_status()
    {
        $type = Spares::TYPE_VOLVO;
        $fileName = 'file_name.txt';
        $model = SparesDownloadFile::createRecord($type, $fileName);

        $this->assertEquals($model->status, SparesDownloadFile::STATUS_DRAFT);

        $model = $model->toggleStatusError();

        $this->assertEquals($model->status, SparesDownloadFile::STATUS_ERROR);
        $this->assertNull($model->error_message);
    }

    /** @test */
    public function toggle_error_status_with_message()
    {
        $type = Spares::TYPE_VOLVO;
        $fileName = 'file_name.txt';
        $model = SparesDownloadFile::createRecord($type, $fileName);

        $this->assertEquals($model->status, SparesDownloadFile::STATUS_DRAFT);

        $errorMsg = 'some error';
        $model = $model->toggleStatusError($errorMsg);

        $this->assertEquals($model->status, SparesDownloadFile::STATUS_ERROR);
        $this->assertEquals($model->error_message, $errorMsg);
    }
}
