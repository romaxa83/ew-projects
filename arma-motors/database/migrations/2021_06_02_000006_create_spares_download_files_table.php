<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSparesDownloadFilesTable extends Migration
{
    public function up()
    {
        Schema::create('spares_download_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type',20);
            $table->string('status', 20)->nullable(\App\Models\Catalogs\Calc\SparesDownloadFile::STATUS_DRAFT);
            $table->string('file_name');
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spares_download_files');
    }
}
