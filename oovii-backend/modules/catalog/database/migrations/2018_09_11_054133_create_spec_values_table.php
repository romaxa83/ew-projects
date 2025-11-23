<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spec_values', function (Blueprint $table) {
            $table->id();
            $table->boolean('published')->default(true);
            $table->foreignId('specification_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->index();
            $table->string('color')->nullable()->default('#000000');
            $table->integer('sort')->default(0)->index();
            $table->timestamps();

            $table->unique(['specification_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spec_values');
    }
}
