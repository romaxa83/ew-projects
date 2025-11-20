<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Core\Enums\TranslationSide;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('namespace')->nullable();
            $table->enum('side', TranslationSide::getValues());
            $table->string('key')->collation('utf8_bin');
            $table->string('locale');
            $table->mediumText('text')->nullable();
            $table->boolean('translated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('translations');
    }
}
