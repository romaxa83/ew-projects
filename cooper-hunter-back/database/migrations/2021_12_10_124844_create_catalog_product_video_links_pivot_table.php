<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_product_video_link_pivot', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('catalog_products')
                ->onDelete('cascade');

            $table->unsignedBigInteger('link_id');
            $table->foreign('link_id')
                ->references('id')
                ->on('catalog_video_links')
                ->onDelete('cascade');

            $table->primary(['product_id', 'link_id'], 'pk-cpvlp_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_video_link_pivot');
    }
};
