<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->boolean('published')->default(true);
            $table->string('group_key')->nullable()->index();
            $table->float('cost', 10, 2)->default(0)->index();
            $table->float('old_cost', 10, 2)->default(0);
            $table->json('videos')->nullable(false);
            $table->boolean('novelty')->default(false)->index();
            $table->boolean('popular')->default(false)->index();
            $table->boolean('sale')->default(false)->index();
            $table->dateTime('expires_at')->nullable();
            $table->unsignedSmallInteger('discount_percentage')->nullable();
            $table->boolean('available')->default(true);
            $table->integer('sort')->default(100)->index();

            $table->softDeletes();
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
        Schema::dropIfExists('products');
    }
}
