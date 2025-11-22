<?php

use App\Models\Catalogs\Calc\Work;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSparesTable extends Migration
{
    public function up()
    {
        Schema::create('spares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->string('type',20);
            $table->string('article');
            $table->string('name');
            $table->bigInteger('price');

            $table->unique(['type', 'article']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('spares');
    }
}
