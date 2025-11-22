<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_product_troubleshoots_pivot', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('catalog_products')
                ->onDelete('cascade');

            $table->unsignedBigInteger('troubleshoot_id');
            $table->foreign('troubleshoot_id')
                ->references('id')
                ->on('catalog_troubleshoots')
                ->onDelete('cascade');

            $table->primary(['product_id', 'troubleshoot_id'], 'pk-cptp_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_troubleshoots_pivot');
    }
};
