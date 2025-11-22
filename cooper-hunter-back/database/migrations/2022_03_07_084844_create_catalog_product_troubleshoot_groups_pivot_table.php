<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_product_troubleshoot_groups_pivot', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id', 'fk-cp')
                ->references('id')
                ->on('catalog_products')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('troubleshoot_group_id');
            $table->foreign('troubleshoot_group_id', 'fk-cptg')
                ->references('id')
                ->on('catalog_troubleshoot_groups')
                ->cascadeOnDelete();

            $table->primary(['product_id', 'troubleshoot_group_id'], 'pk-cptp_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_troubleshoot_groups_pivot');
    }
};
